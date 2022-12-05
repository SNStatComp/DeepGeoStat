import time
from collections import Counter

import numpy as np
import pandas as pd


def get_label_evidence_for_grid_cell(grids, label_evidence_list, evidence_type, confidence_reason,
                                     default_label_class=None):
    grid_amount = len(grids)
    start_time = time.time()
    print("\nCreating numpy array")
    label_evidence_output = []

    label_evidence_array = np.empty([0, grid_amount, 2], dtype="int64")
    for annotation_campaign in label_evidence_list:
        if not annotation_campaign:
            annotation_campaign.append([0, 0])
        annotation_campaign_df = pd.DataFrame(annotation_campaign).values

        if len(annotation_campaign_df) != grid_amount:
            offset = grid_amount - len(annotation_campaign_df)
            annotation_campaign_df = np.append(annotation_campaign_df, np.zeros([offset, 2], dtype="int64"), axis=0)

        label_evidence_array = np.append(label_evidence_array, [annotation_campaign_df], axis=0)

    print(f"Time to create numpy array of registers/annotations: "
          f"{time.time() - start_time} seconds; shape: {label_evidence_array.shape}")

    start_time = time.time()
    print("\nConsolidating")
    for grid_cell_id in grids:
        index = np.where(label_evidence_array == grid_cell_id)
        label_evidence = list(label_evidence_array[index[0], index[1], 1])
        if default_label_class:
            # If the amount of other label evidence found for the selected grid is not equal to the amount
            # of other label evidence sources there are default values, so these need to be added.
            if evidence_type == "other":
                annotation_amount = len(label_evidence_array)
            elif evidence_type == "register":
                annotation_amount = 1

            if annotation_amount > len(label_evidence) and default_label_class:
                for i in range(0, (len(label_evidence_list) - len(label_evidence))):
                    label_evidence.append(default_label_class)

        if label_evidence:
            label_class_id, confidence = get_majority_vote(label_evidence)

            # Add the consolidated Label Class with the associated values.
            label_evidence_output.append({
                'grid_id': int(grid_cell_id),
                'label_class_id': int(label_class_id),
                'confidence': round(confidence * 100, 1),
                'confidence_reason': confidence_reason
            })

    print(f"Time to create consolidate: {time.time() - start_time} seconds")

    # Return the Consolidated Dataset in JSON.
    return label_evidence_output


def get_majority_vote(label_evidence):
    counter = Counter(label_evidence).most_common(1)

    label_class_id = counter[0][0]
    confidence = round(counter[0][1] / len(label_evidence), 2)

    return label_class_id, confidence


def consolidate_dataset(default_label_class, label_classes, grids, label_evidence):
    label_evidence_output = {}

    # Get associated register evidence for grid.
    if label_evidence['registers']:
        confidence_reason = "Register Evidence"
        label_evidence_output = get_label_evidence_for_grid_cell(grids,
                                                                 label_evidence['registers'],
                                                                 "register",
                                                                 confidence_reason,
                                                                 default_label_class)
    # If no Register evidence found the Annotation Campaign evidence will be used.
    elif label_evidence['other']:
        confidence_reason = "Annotation Campaign Majority Vote"
        label_evidence_output = get_label_evidence_for_grid_cell(grids,
                                                                 label_evidence['other'],
                                                                 "other",
                                                                 confidence_reason,
                                                                 default_label_class)

    # Return the Consolidated Dataset in JSON.
    return {
        'labels': label_evidence_output,
    }