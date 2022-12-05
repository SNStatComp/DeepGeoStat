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
from sklearn.preprocessing import LabelEncoder
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

    def exit_gracefully(self, signum, frame):
        self.kill_now = True


class DoubleGenerator(tf.keras.utils.Sequence):
    def __init__(self, generator_1, generator_2):
        self.generator_1 = generator_1
        self.generator_2 = generator_2

    def __len__(self):
        return len(self.generator_1)

    def __getitem__(self, i):
        x_1, y = self.generator_1[i]
        x_2, y = self.generator_2[i]

        return (x_1, x_2), y


def euclidian_distance(inputs):
    (x, y) = inputs

    sum_squared = tf.math.reduce_sum(tf.math.square(x - y), axis=1, keepdims=True)
    return tf.math.sqrt(tf.math.maximum(sum_squared, tf.keras.backend.epsilon()))


def siamese_model(model):
    input_1 = tf.keras.Input(shape=(image_size, image_size, 3))
    feats_1 = model(input_1)

    input_2 = tf.keras.Input(shape=(image_size, image_size, 3))
    feats_2 = model(input_2)

    concat = tf.keras.layers.Lambda(euclidian_distance)([feats_1, feats_2])
    flatten = tf.keras.layers.Flatten()(concat)
    output = tf.keras.layers.Dense(1, activation='sigmoid')(flatten)

    return tf.keras.Model(inputs=[input_1, input_2], outputs=output)


def contrastive_loss(y, preds, margin=1):
    """
    loss function that discriminates the features of a pair of input vectors, outputs 1 if similar otherwise 0
    """
    y = tf.cast(y, preds.dtype)
    squaredPreds = tf.math.square(preds)
    squaredMargin = tf.math.square(tf.math.maximum(margin - preds, 0))
    loss = tf.math.reduce_mean((1 - y) * squaredPreds + (y) * squaredMargin)
    return loss


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
    '''
        Create a dataset for change detection with path to images for both years and the label
        :param the image data used
        :return: dataframe with all information
    '''
    if not data:
        return None, None

    label_encoder = LabelEncoder()
    label_encoder.fit(label_classes)

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

    y_data = label_encoder.transform(y_data)
    dataframe = pd.DataFrame(
        data={'year_1': np.array(x_data)[:, 0], 'year_2': np.array(x_data)[:, 1], 'label': np.array(y_data)})
    return dataframe


if experiment_data['test']:
    # Test data has been selected so use this and 10% of train dataset as validation set.
    train_data = create_dataset(experiment_data['train'])
    x_train, x_validation, y_train, y_validation = train_test_split(
        train_data,
        test_size=0.1,
        random_state=0,
    )
    test_data = create_dataset(experiment_data['test'])
    x_test = test_data
    y_test = test_data['label']
else:
    # Test data has not been specified, use 20% of the train data for testing and 10% for validation.
    dataframe = create_dataset(experiment_data['train'])
    y = dataframe['label'].to_numpy()
    x_train, x_test, y_train, y_test = train_test_split(
        dataframe,
        y,
        test_size=0.2,
        random_state=0,
    )
    x_train, x_validation, y_train, y_validation = train_test_split(
        x_train,
        y_train,
        test_size=0.1,
        random_state=0,
    )

generator = ImageDataGenerator(rescale=1. / 255)

train_ds_1 = generator.flow_from_dataframe(dataframe=x_train,
                                           x_col='year_1',
                                           y_col='label',
                                           class_mode='raw',
                                           target_size=(image_size, image_size),
                                           interpolation="bilinear",
                                           batch_size=batch_size,
                                           shuffle=False,
                                           seed=6235053,
                                           validate_filenames=True)

train_ds_2 = generator.flow_from_dataframe(dataframe=x_train,
                                           x_col='year_2',
                                           y_col='label',
                                           class_mode='raw',
                                           target_size=(image_size, image_size),
                                           interpolation="bilinear",
                                           batch_size=batch_size,
                                           shuffle=False,
                                           seed=6235053,
                                           validate_filenames=True)

validation_ds_1 = generator.flow_from_dataframe(dataframe=x_validation,
                                                x_col='year_1',
                                                y_col='label',
                                                class_mode='raw',
                                                target_size=(image_size, image_size),
                                                interpolation="bilinear",
                                                batch_size=batch_size,
                                                shuffle=False,
                                                seed=6235053,
                                                validate_filenames=True)

validation_ds_2 = generator.flow_from_dataframe(dataframe=x_validation,
                                                x_col='year_2',
                                                y_col='label',
                                                class_mode='raw',
                                                target_size=(image_size, image_size),
                                                interpolation="bilinear",
                                                batch_size=batch_size,
                                                shuffle=False,
                                                seed=6235053,
                                                validate_filenames=True)

test_ds_1 = generator.flow_from_dataframe(dataframe=x_test,
                                          x_col='year_1',
                                          y_col='label',
                                          class_mode='raw',
                                          target_size=(image_size, image_size),
                                          interpolation="bilinear",
                                          batch_size=batch_size,
                                          shuffle=False,
                                          seed=6235053,
                                          validate_filenames=True)

test_ds_2 = generator.flow_from_dataframe(dataframe=x_test,
                                          x_col='year_2',
                                          y_col='label',
                                          class_mode='raw',
                                          target_size=(image_size, image_size),
                                          interpolation="bilinear",
                                          batch_size=batch_size,
                                          shuffle=False,
                                          seed=6235053,
                                          validate_filenames=True)

train_ds = DoubleGenerator(train_ds_1, train_ds_2)
validation_ds = DoubleGenerator(validation_ds_1, validation_ds_2)
test_ds = DoubleGenerator(test_ds_1, test_ds_2)

# Make model.
model = siamese_model(
    {% if model == 'resnet50' %}
        tf.keras.applications.ResNet50(
        include_top=False,
        input_shape=(image_size, image_size, 3),
        weights='imagenet',
    )
    {% elif model == 'vgg16' %}
        tf.keras.applications.VGG16(
        include_top=False,
        input_shape=(image_size, image_size, 3),
        weights='imagenet',
        )
    {% elif model == 'inceptionv3' %}
        tf.keras.applications.InceptionV3(
        include_top=False,
        input_shape=(image_size, image_size, 3),
        weights='imagenet',
        )
    {% endif %}
)

model.compile(
    optimizer=tf.keras.optimizers.Adam(
        learning_rate={{learning_rate}}
    ),
    loss=contrastive_loss,
    metrics=["accuracy",
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
# y_true = list(map(lambda y: label_classes[y], np.argmax(y_test, axis=1)))
# y_pred = list(map(lambda prediction: label_classes[prediction], np.argmax(model.predict(test_ds), axis=1)))
y_true = list(map(lambda y: label_classes[y], np.around(y_test)))
y_pred = list(map(lambda prediction: label_classes[round(prediction)], model.predict(test_ds)[:, 0]))

test_confusion_matrix = sklearn.metrics.confusion_matrix(y_true, y_pred, labels=label_classes)

if (precision + recall) == 0:
    f1_score = 0
else:
    f1_score = 2 * ((precision * recall) / (precision + recall))

data = {
    "model_statistics": {
        "accuracy": round(accuracy * 100, 1),
        "precision": round(precision * 100, 1),
        "recall": round(recall * 100, 1),
        "f1_score": round(f1_score * 100, 1),
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
