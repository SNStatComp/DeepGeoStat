<?php

namespace App\Enums;

enum ExperimentModelTypeEnum: string
{
    case ResNet50 = 'resnet50';
    case VGG16 = 'vgg16';
    case InceptionV3 = 'inceptionv3';
}
