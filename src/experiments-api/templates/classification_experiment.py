import csv
import json
import os
import pathlib
import signal

import pandas as pd
import numpy as np
import sklearn
import tensorflow as tf
from sklearn.model_selection import train_test_split
from tensorflow.keras.preprocessing.image import ImageDataGenerator

experiment_data = {{experiment_data}}
label_classes = {{label_classes}}
batch_size = {{batch_size}}
experiment_directory = "/usr/deepgeostat-ai/experiments/{{ experiment_id }}"
container_per_gpu = os.environ.get("CONTAINER_PER_GPU")
image_size = 500  # TODO: should be derived from interface/dataset

gpus = tf.config.list_physical_devices('GPU')
if gpus:
    print(f"GPU that will be used: {gpus[0]}")
    config = tf.compat.v1.ConfigProto()
    config.gpu_options.allow_growth = True
    config.gpu_options.per_process_gpu_memory_fraction = (1 / int(container_per_gpu))
    session = tf.compat.v1.Session(config=config)
else:
    print("CPU will be used")


class GracefulKiller:
    kill_now = False

    def __init__(self):
        signal.signal(signal.SIGINT, self.exit_gracefully)
        signal.signal(signal.SIGTERM, self.exit_gracefully)

    def exit_gracefully(self, *args):
        self.kill_now = True


def get_grid_image(grid):
    '''
    Get the path to a grid image on the dgx server
    :param grid: the grid id
    :return: the path
    '''
    img_folder = "/usr/deepgeostat-ai/images/" \
                 f"grid_{grid['dataset']['grid_cell_size']}m/" \
                 f"{grid['dataset']['year']}_{grid['dataset']['image_type']}_tf"

    if grid.get("mask_index"):
        img_path = f"{img_folder}/{grid['grid_cell']}_{grid['dataset']['year']}_" \
                   f"{grid['dataset']['image_type'].upper()}_{grid['mask_index']}.tiff"
    else:
        img_path = f"{img_folder}/{grid['grid_cell']}_{grid['dataset']['year']}_" \
                   f"{grid['dataset']['image_type'].upper()}.tiff"

    return img_path


def create_dataset(data):
    if not data:
        return None, None
    x_data = []
    y_data = []
    for label in data:
        label_x_data = []
        for grid in label['grids']:
            label_x_data.append(get_grid_image(grid))

        if len(label_x_data) == 1:
            x_data.append(label_x_data[0])
        else:
            x_data.append(label_x_data)

        y_data.append(label['label'])

    dataframe = pd.DataFrame(data={'image': np.array(x_data), 'label': y_data})
    return dataframe


# Create train, validation and test data.
if experiment_data['test']:
    # Test data has been selected so use this and 10% of train dataset as validation set.
    d = create_dataset(experiment_data['train'])
    x_train, x_validation, y_train, y_validation = train_test_split(d, d['label'], test_size=0.1, random_state=0, )
    d_test = create_dataset(experiment_data['test'])
    x_test = d_test
    y_test = d_test['label']
else:
    # Test data has not been specified, use 20% of the train data for testing and 10% for validation.
    d = create_dataset(experiment_data['train'])
    x_train, x_test, y_train, y_test = train_test_split(d, d['label'], test_size=0.2, random_state=0, )
    x_train, x_validation, y_train, y_validation = train_test_split(x_train, y_train, test_size=0.1, random_state=0, )

generator = ImageDataGenerator(rescale=1. / 255)

train_ds = generator.flow_from_dataframe(dataframe=x_train,
                                         x_col='image',
                                         y_col='label',
                                         class_mode="categorical",
                                         target_size=(image_size, image_size),
                                         interpolation="bilinear",
                                         batch_size=batch_size,
                                         shuffle=False,
                                         seed=6235053,
                                         validate_filenames=True)

validation_ds = generator.flow_from_dataframe(dataframe=x_validation,
                                              x_col='image',
                                              y_col='label',
                                              class_mode="categorical",
                                              target_size=(image_size, image_size),
                                              interpolation="bilinear",
                                              batch_size=batch_size,
                                              shuffle=False,
                                              seed=6235053,
                                              validate_filenames=True)

test_ds = generator.flow_from_dataframe(dataframe=x_test,
                                        x_col='image',
                                        y_col='label',
                                        class_mode="categorical",
                                        target_size=(image_size, image_size),
                                        interpolation="bilinear",
                                        batch_size=batch_size,
                                        shuffle=False,
                                        seed=6235053,
                                        validate_filenames=True)

# Make model.
input_layer = tf.keras.Input(shape=(image_size, image_size, 3))
{% if model == 'resnet50' %}
transfer_learning_layer = tf.keras.applications.ResNet50(
    include_top=False,
    input_shape=(image_size, image_size, 3),
    weights='imagenet',
)(input_layer)
{% elif model == 'vgg16' %}
transfer_learning_layer = tf.keras.applications.VGG16(
    include_top=False,
    input_shape=(image_size, image_size, 3),
    weights='imagenet',
)(input_layer)
{% elif model == 'inceptionv3' %}
transfer_learning_layer = tf.keras.applications.InceptionV3(
    include_top=False,
    input_shape=(image_size, image_size, 3),
    weights='imagenet',
)(input_layer)
{% endif %}
flatten = tf.keras.layers.Flatten()(transfer_learning_layer)
dense = tf.keras.layers.Dense(len(label_classes), activation="sigmoid")(flatten)
model = tf.keras.Model(inputs=input_layer, outputs=dense)

flatten = tf.keras.layers.Flatten()(transfer_learning_layer)
dense = tf.keras.layers.Dense(len(label_classes), activation="sigmoid")(flatten)
model = tf.keras.Model(inputs=input_layer, outputs=dense)

model.compile(
    optimizer=tf.keras.optimizers.Adam(
        learning_rate={{learning_rate}}
    ),
    loss=tf.keras.losses.CategoricalCrossentropy(),
    metrics=['accuracy',
             tf.keras.metrics.Precision(),
             tf.keras.metrics.Recall()],
)


class TrainingCurveCallback(tf.keras.callbacks.Callback):
    def on_train_begin(self, logs=None):
        self.killer = GracefulKiller()
        self.batch_amount = len(train_ds)
        self.epoch = 0

        csv_columns = ['epoch', 'type', 'value']
        self.directory = pathlib.Path(f"{experiment_directory}/learning_history.csv")

        with open(self.directory, "w+", newline="") as file:
            writer = csv.writer(file)
            writer.writerow(csv_columns)

    def on_epoch_end(self, epoch, logs=None):
        self.epoch = (epoch + 1)
        with open(self.directory, "a", newline="") as file:
            writer = csv.writer(file)
            for key, value in logs.items():
                writer.writerow([self.epoch, key, value])
        if self.killer.kill_now:
            self.model.stop_training = True

    def on_train_batch_end(self, batch, logs=None):
        if batch == (self.batch_amount - 1):
            return

        batch_number = ((batch + 1) / self.batch_amount) + self.epoch

        with open(self.directory, "a", newline="") as file:
            writer = csv.writer(file)
            for key, value in logs.items():
                writer.writerow([batch_number, key, value])
        if self.killer.kill_now:
            self.model.stop_training = True


# early_stopping = tf.keras.callbacks.EarlyStopping(
#     monitor='val_loss',
#     patience={{ early_stopping }},
# )


# Train the model.
history = model.fit(
    train_ds,
    validation_data=validation_ds,
    epochs={{epochs}},
    callbacks=[TrainingCurveCallback()],
)

# Evaluate the trained model.
_, accuracy, precision, recall = model.evaluate(
    test_ds,
)

# Save the trained model.
model.save(
    f"{experiment_directory}/model",
    overwrite=True,
)

# Save Test Confusion Matrix.
y_pred = list(map(lambda prediction: label_classes[prediction], np.argmax(model.predict(test_ds), axis=1)))
test_confusion_matrix = sklearn.metrics.confusion_matrix(y_test, y_pred, labels=label_classes)

if (precision + recall) == 0:
    f1_score = 0
else:
    f1_score = 2 * ((precision * recall) / (precision + recall))

data = {
    "model_statistics": {
        "accuracy": round(accuracy * 100, 1),
        "precision": round(precision * 100, 1),
        "recall": round(recall * 100, 1),
        "f1_score": round(f1_score * 100, 1)
    },
    "confusion_matrix": {
        "labels": label_classes,
        "matrices": {
            "test": test_confusion_matrix.tolist()
        }
    }
}

directory = pathlib.Path(f"{experiment_directory}/experiment_output.json")
with open(directory, "w+") as file:
    json.dump(data, file, indent=2)
