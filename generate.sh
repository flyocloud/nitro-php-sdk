#!/bin/sh
openapi-generator-cli generate -i https://edgeapi.flyo.cloud/nitro/openapi \
    -g php \
    --additional-properties=invokerPackage=Flyo