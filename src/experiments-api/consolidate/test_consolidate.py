import json
import unittest

import dataset_consolidation

label_classes = [1, 2]
grids = [1]


class TestConsolidateDatasets(unittest.TestCase):

    def test_same_label_dataset_consolidate_only_register(self):
        default_label_class = None
        label_evidence = {
            'registers': [
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 1,
                        'weight': None,
                    },
                ],
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 1,
                        'weight': None,
                    },
                ],
            ],
            'other': [],
        }

        output = json.loads(
            dataset_consolidation.consolidate_dataset(default_label_class, label_classes, grids, label_evidence))[
            'labels']

        self.assertEqual(output[0]['grid_id'], 1)
        self.assertEqual(output[0]['label_class_id'], 1)
        self.assertEqual(output[0]['confidence'], 1)

    def test_same_label_dataset_consolidate_only_annotation_campaign(self):
        default_label_class = None
        label_evidence = {
            'registers': [],
            'other': [
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 1,
                        'weight': None,
                    },
                ],
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 1,
                        'weight': None,
                    },
                ],
            ],
        }

        output = json.loads(
            dataset_consolidation.consolidate_dataset(default_label_class, label_classes, grids, label_evidence))[
            'labels']

        self.assertEqual(output[0]['grid_id'], 1)
        self.assertEqual(output[0]['label_class_id'], 1)
        self.assertEqual(output[0]['confidence'], 1)

    def test_same_label_dataset_consolidate_register_and_annotation_campaign(self):
        default_label_class = None
        label_evidence = {
            'registers': [
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 1,
                        'weight': None,
                    },
                ],
            ],
            'other': [
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 1,
                        'weight': None,
                    },
                ],
            ],
        }

        output = json.loads(
            dataset_consolidation.consolidate_dataset(default_label_class, label_classes, grids, label_evidence))[
            'labels']

        self.assertEqual(output[0]['grid_id'], 1)
        self.assertEqual(output[0]['label_class_id'], 1)
        self.assertEqual(output[0]['confidence'], 1)

    def test_different_label_dataset_consolidate_only_register(self):
        default_label_class = None
        label_evidence = {
            'registers': [
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 1,
                        'weight': None,
                    },
                ],
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 1,
                        'weight': None,
                    },
                ],
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 2,
                        'weight': None,
                    },
                ],
            ],
            'other': [],
        }

        output = json.loads(
            dataset_consolidation.consolidate_dataset(default_label_class, label_classes, grids, label_evidence))[
            'labels']

        self.assertEqual(output[0]['grid_id'], 1)
        self.assertEqual(output[0]['label_class_id'], 1)
        self.assertEqual(output[0]['confidence'], 0.67)

    def test_different_label_dataset_consolidate_only_annotation_campaign(self):
        default_label_class = None
        label_evidence = {
            'registers': [],
            'other': [
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 1,
                        'weight': None,
                    },
                ],
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 1,
                        'weight': None,
                    },
                ],
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 2,
                        'weight': None,
                    },
                ],
            ],
        }

        output = json.loads(
            dataset_consolidation.consolidate_dataset(default_label_class, label_classes, grids, label_evidence))[
            'labels']

        self.assertEqual(output[0]['grid_id'], 1)
        self.assertEqual(output[0]['label_class_id'], 1)
        self.assertEqual(output[0]['confidence'], 0.67)

    def test_different_label_dataset_consolidate_register_and_annotation_campaign(self):
        default_label_class = None
        label_evidence = {
            'registers': [
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 1,
                        'weight': None,
                    },
                ],
            ],
            'other': [
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 2,
                        'weight': None,
                    },
                ],
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 2,
                        'weight': None,
                    },
                ],
            ],
        }

        output = json.loads(
            dataset_consolidation.consolidate_dataset(default_label_class, label_classes, grids, label_evidence))[
            'labels']

        self.assertEqual(output[0]['grid_id'], 1)
        self.assertEqual(output[0]['label_class_id'], 1)
        self.assertEqual(output[0]['confidence'], 1)

    def test_consolidate_only_annotation_campaign_with_empty_annotation_campaign(self):
        default_label_class = None
        label_evidence = {
            'registers': [],
            'other': [
                [],
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 1,
                        'weight': None,
                    },
                ],
            ],
        }

        output = json.loads(
            dataset_consolidation.consolidate_dataset(default_label_class, label_classes, grids, label_evidence))[
            'labels']

        self.assertEqual(output[0]['grid_id'], 1)
        self.assertEqual(output[0]['label_class_id'], 1)
        self.assertEqual(output[0]['confidence'], 1)

    def test_only_default_label_dataset_consolidate_only_annotation_campaign(self):
        default_label_class = 1
        label_evidence = {
            'registers': [],
            'other': [
                [],
            ],
        }

        output = json.loads(
            dataset_consolidation.consolidate_dataset(default_label_class, label_classes, grids, label_evidence))[
            'labels']

        self.assertEqual(output[0]['grid_id'], 1)
        self.assertEqual(output[0]['label_class_id'], 1)
        self.assertEqual(output[0]['confidence'], 1)

    def test_default_label_and_other_labels_dataset_consolidate_only_annotation_campaign(self):
        default_label_class = 1
        label_evidence = {
            'registers': [],
            'other': [
                [],
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 1,
                        'weight': None,
                    },
                ],
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 2,
                        'weight': None,
                    },
                ],
            ],
        }

        output = json.loads(
            dataset_consolidation.consolidate_dataset(default_label_class, label_classes, grids, label_evidence))[
            'labels']

        self.assertEqual(output[0]['grid_id'], 1)
        self.assertEqual(output[0]['label_class_id'], 1)
        self.assertEqual(output[0]['confidence'], 0.67)

    def test_default_label_dataset_consolidate_register_and_annotation_campaign(self):
        default_label_class = 1
        label_evidence = {
            'registers': [
                [],
            ],
            'other': [
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 1,
                        'weight': None,
                    },
                ],
                [
                    {
                        'grid_id': 1,
                        'label_class_id': 2,
                        'weight': None,
                    },
                ],
            ],
        }

        output = json.loads(
            dataset_consolidation.consolidate_dataset(default_label_class, label_classes, grids, label_evidence))[
            'labels']

        self.assertEqual(output[0]['grid_id'], 1)
        self.assertEqual(output[0]['label_class_id'], 1)
        self.assertEqual(output[0]['confidence'], 0.5)


if __name__ == '__main__':
    unittest.main(verbosity=2)
