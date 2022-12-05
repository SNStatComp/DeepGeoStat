FROM python:3.10

ENV TZ=Europe/Amsterdam \
    DEBIAN_FRONTEND=noninteractive

WORKDIR /usr/deepgeostat-api/app

COPY ./ /usr/deepgeostat-api/app

RUN apt-get update \
 && apt-get dist-upgrade -y \
 && apt-get install -y \
    curl \
    ffmpeg \
    libsm6 \
    libxext6 \
    python3-pip \
    python3-distutils \
    software-properties-common \
 && python3.10 -m pip install --no-cache-dir --upgrade pip \
 && python3.10 -m pip install --no-cache-dir --upgrade -r /usr/deepgeostat-api/app/dockerfiles/api/requirements.txt \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* \
           /usr/deepgeostat-api/app/dockerfiles