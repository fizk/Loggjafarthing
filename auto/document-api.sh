#!/usr/bin/env bash

SCRIPT=$( cd "$( dirname "$0" )" && pwd )/../public/index.php

php ${SCRIPT} document:api
