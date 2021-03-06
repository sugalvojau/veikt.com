#!/usr/bin/env bash
id

echo -e "\n PUBLICIZE: Started: $(date +%Y-%m-%d:%H:%M:%S)"

# TODO: Ignore publicizing the downloaded and normalized queue that failed to download or normalize...?

# Get dir of this script
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# The php file this script should run
INDEX_FILE_PHP="code/php/unpublish.php"

# Php
PHP_FILE_PRODUCTION_ENV="php-cli"
# Local php config file
PHP_CONFIG_FILE_PRODUCTION_ENV="/home/antdelno/php_extensions/php.ini"

# Run php with local php configuration if config file exists
if [ -f $PHP_CONFIG_FILE_PRODUCTION_ENV ];
then
   $PHP_FILE_PRODUCTION_ENV -c PHP_CONFIG_FILE_PRODUCTION_ENV -f "$DIR/$INDEX_FILE_PHP"
else
   php -f "$DIR/$INDEX_FILE_PHP"
fi


echo -e "\n PUBLICIZE: Finished: $(date +%Y-%m-%d:%H:%M:%S)"
