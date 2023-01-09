# DeepGeoStat

#### The starting point of project

Statistics Netherlands (SN) has huge amounts of data, collected through surveys and/or via (3rd party) administrative data. This data is used for computing official statistics. But collecting this data may be time consuming and costly.
      
Each year, SN receives aerial and satellite images - Earth Observation (EO) data - of the Netherlands. Some statistical concepts may be automatically derived (or classified) from such data. 
      
The field of Deep Learning (DL) has made great strides with respect to image classification. Classifying images automatically can save huge amounts of time (as compared to manual inspection). However, working with big data and the bleeding edge of DL requires niche IT- and data-science skills, skills that people at statistical divisions at SN typically don’t have.

The above facts lead to this project. The goals of the Eurostat funded project were twofold. First, we researched new methodologies - using DL on EO Data - that can automatically generate official statistics, or make the process of creating official statistics more efficient. Secondly, proven technology was made available to the end users at statistical divisions via a user-friendly tool.

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

# DeepGeoStat

This is the repository for the DeepGeoStat project. DeepGeoStat uses Docker containers to run its services.
The project consists of two main components for which separate Docker containers are built:

  * The web application;
  * The API.

# Setup

## Automated script
To set up all the required docker containers, you can run the automated shell script `setup.sh` in the `src/` folder.
Navigate to the `src` in the unix terminal and run the following command. **Make sure you run the command with sudo.**:

```shell
sudo sh setup.sh
```

In case you want to complete **nuke all** your existing docker containers, you can append the `--force` or shorthand `-f` flag to the command likek the following below.
**WARNING: THIS OPTION REMOVES ALL YOUR EXISTING DOCKER CONTAINERS SAVED BY DOCKER.**.

```shell
sudo sh setup.sh --force
```

The script should automate the complete process and setup up all the Docker containers.

In case any of the steps in the build script fails, you can try to run the build scripts separately for the web-app and API containers:

```shell
# Setup docker containers for the webapp
# In the src/web-app folder:
sudo sh build_web_app.sh
```

```shell
# Setup docker containers for the api
# In the src/experiments-api folder:
sudo sh build_api.sh
```
## Manual install
If the automated script happens to fail, you can follow the steps below to troubleshoot or to manually build the docker containers from scratch.

1. First of all, clone the directory onto your local machine:
```shell
git clone git@gitlab.com:CBDS/deepgeostat.git
```

### Adding grid image directory
1. Make sure to add the directory named `images` with grid image in the `src/experiments-api` folder. The images are therefore expected to be found in:
```shell
src/experiments-api/images/
```

### Setting up the Web App
1. Navigate to the `web-app` folder and copy `.env.default` to `.env`.
```shell
cd web-app \
&& cp .env.default .env
```

2. Build an installer Docker container.
```shell
docker compose -f docker-compose.dev.yml up installer -d
```

3. Run the following commands to install the PHP dependencies via `composer` and `npm`.
```shell
docker exec web-app-installer composer install
docker exec web-app-installer npm install
```

4. Stop `installer` container.
```shell
docker compose -f docker-compose.dev.yml --profile installer down
```

5. Launch the Docker container for the web-app.
```shell
docker compose -f docker-compose.dev.yml up -d
```

6. Generate the artisan key required for Laravel.
```shell
docker exec --user 0 web-app-serve-1 /bin/bash -c "chown -R www-data:www-data /var/www" \
&& docker exec web-app-serve-1 /bin/bash -c "php artisan key:generate"
```

7. Load the data onto your docker container.
```shell
docker exec web-app-serve-1 php artisan migrate --seed
```

The containers for the web-app should be now completely set up.

### Setting up the API
1. Navigate to the `experiments-api` and copy the `.env.default` file to `.env`.
```shell
cd experiments-api \
&& cp .env.default .env
```

2. Lastly, simply run the following command to launch the Docker container.
```shell
docker compose -f docker-compose.dev.yml up -d
```
