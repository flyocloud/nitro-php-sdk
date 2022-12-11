#!/bin/sh
openapi-generator-cli generate -i https://api.flyo.dev/nitro/openapi \
    -g php \
    --additional-properties=invokerPackage=Flyo