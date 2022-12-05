import json
import os
import pathlib
from datetime import datetime

import pandas as pd
import numpy as np
import tensorflow as tf
from tensorflow.keras.preprocessing.image import ImageDataGenerator

# some settings
prediction_data = {{prediction_data}}
label_classes = {{label_classes}}
experiment_directory = "/usr/deepgeostat-ai/experiments/{{ experiment_id }}"
container_per_gpu = os.environ.get("CONTAINER_PER_GPU")
output_file_prefix = os.environ.get("OUTPUT_FILE_PREFIX", datetime.today().strftime('%Y-%m-%d %H:%M:%S'))
image_size = 500  # TODO: should be derived from interface/dataset

# some generic functions
gpus = tf.config.list_physical_devices('GPU')
if gpus:
    print(f"GPU that will be used: {gpus[0]}")
    config = tf.compat.v1.ConfigProto()
    config.gpu_options.allow_growth = True
    config.gpu_options.per_process_gpu_memory_fraction = (1 / int(container_per_gpu))
    session = tf.compat.v1.Session(config=config)
else:
    print("CPU will be used")

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

# the image data generator, used in all experiments
generator = ImageDataGenerator(rescale=1. / 255)

print("Loading images (paths)...")

prediction_images = []
for grid in prediction_data:
    grid_images = []
    for grid_cell in grid['grids']:
        grid_images.append(get_grid_image(grid_cell))

    if len(grid_images) == 1:
        prediction_images.append(grid_images[0])
    else:
        prediction_images.append(grid_images)

print("Prediction images:", len(prediction_images))

# collect predictions here
predictions = []

# the script depending on the type of experiment
{% if experiment_type == 'change_detection' %}

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


# labels are unknown, just put zeros there
prediction_images_y = np.zeros(shape=len(prediction_images))

# put paths for both years in dataframe, and the labels
prediction_df = pd.DataFrame(data={'year_1': np.array(prediction_images)[:, 0], 'year_2': np.array(prediction_images)[:, 1],'label': prediction_images_y})

# generator for first years images
prediction_image_data_1 = generator.flow_from_dataframe(dataframe=prediction_df,
                                            x_col='year_1',
                                            y_col='label',
                                            class_mode='raw',
                                            target_size=(image_size, image_size),
                                            interpolation="bilinear",
                                            batch_size=32,
                                            shuffle=False,
                                            seed=6235053,
                                            validate_filenames=True)

# generator for second years images
prediction_image_data_2 = generator.flow_from_dataframe(dataframe=prediction_df,
                                            x_col='year_2',
                                            y_col='label',
                                            class_mode='raw',
                                            target_size=(image_size, image_size),
                                            interpolation="bilinear",
                                            batch_size=32,
                                            shuffle=False,
                                            seed=6235053,
                                            validate_filenames=True)

# the final generator that combines input for both years
prediction_ds = DoubleGenerator(prediction_image_data_1, prediction_image_data_2)

print("Done with creating change detection prediction dataset...")

model = tf.keras.models.load_model(f"{experiment_directory}/model", custom_objects={'contrastive_loss': contrastive_loss})

print("Done loading model...")

for _, prediction in enumerate(
        list(map(lambda prediction: label_classes[round(prediction)], model.predict(prediction_ds)[:, 0]))):
    predictions.append({
        'label': prediction,
    })

{% endif %}


{% if experiment_type == 'classification' %}

prediction_df = pd.DataFrame(data={'image': np.array(prediction_images)})

# the final generator for one year data
prediction_ds = generator.flow_from_dataframe(dataframe=prediction_df,
                                             x_col='image',
                                             #y_col=label_classes,
                                             class_mode=None,
                                             target_size=(image_size, image_size),
                                             interpolation="bilinear",
                                             batch_size=32,
                                             shuffle=False,
                                             seed=6235053,
                                             validate_filenames=True)

print("Done with creating classification prediction dataset...")

model = tf.keras.models.load_model(f"{experiment_directory}/model")

print("Done loading model...")

for _, prediction in enumerate(
        list(map(lambda prediction: label_classes[prediction], np.argmax(model.predict(prediction_ds), axis=1)))):
    predictions.append({
        'label': prediction,
    })

{% endif %}

data = {
    'predictions': predictions,
}

print("Saving predictions...")

directory = pathlib.Path(f"{experiment_directory}/prediction_output_{output_file_prefix}.json")
with open(directory, "w+") as file:
    json.dump(data, file, indent=2)