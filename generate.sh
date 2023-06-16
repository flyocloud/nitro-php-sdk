#!/bin/sh
openapi-generator-cli generate -i https://api.flyo.cloud/nitro/v1/openapi \
    -g php \
    -t templates \
    --additional-properties=invokerPackage=Flyo