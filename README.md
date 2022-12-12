# DeepGeoStat

#### The starting point of project

Statistics Netherlands (SN) has huge amounts of data, collected through surveys and/or via (3rd party) administrative data. This data is used for computing official statistics. But collecting this data may be time consuming and costly.

			+
      
Each year, SN receives aerial and satellite images - Earth Observation (EO) data - of the Netherlands. Some statistical concepts may be automatically derived (or classified) from such data. 

			+
      
The field of Deep Learning (DL) has made great strides with respect to image classification. Classifying images automatically can save huge amounts of time (as compared to manual inspection). However, working with big data and the bleeding edge of DL requires niche IT- and data-science skills, skills that people at statistical divisions at SN typically don’t have.

			=
      
The goals of the Eurostat funded project were twofold. First, we researched new methodologies - using DL on EO Data - that can automatically generate official statistics, or make the process of creating official statistics more efficient. Secondly, proven technology was made available to the end users at statistical divisions via a user-friendly tool.

#### The niche of the tool

* User-friendly application of Deep Learning on Earth Observation Data for the task of creating official statistics (more efficiently)

* Constrained usage of DL (based on best practices researched by SN’s Methodology team).

* Seamless integration with SN’s geo standards (i.e., provinces, cities, districts, neighborhoods and grid statistics) and data (i.e., surface usage, grid statistics and more.

* Active learning component via annotation campaigns.

* Focus on validation and tailored application of DL model for the task of creation of official statistics (more efficiently).

### The workflow of tool

DeepGeoStat enforces a strict workflow, where most steps include different methodological challenges. These challenges are researched by SN’s methodology department and the resulting best practices are implemented in the DeepStat workflow. These challenges include effective annotation and consolidation strategies, tailored deep learning network structures and learning strategies, and sound validation (explanation) of model output.

1 - Project: 

Decide the project methodology and target labels (i.e., what is that you want to classify?).

2 - Data:

Select (sample) image data (X) from fixed set of aerial- and satellite-image datasets.
Collect so-called label evidence (y’i...x) via registers, custom label sets or via annotation campaigns.

3 - Inspect:

Consolidate the label evidence (y’i...x) to get final labels (y) and inspect quality of image-label pairs {X,y}
If image-label pairs are of good quality, save (subset of) data as so-called experiment data. If image-label pairs are flawed, revisit step 2.

4 - Experiments:

Test and validate various pre-configured convolutional deep learning networks on the experiment data
Export country-wide model predictions (csv), input for the (more efficient) creation of official statistics.
