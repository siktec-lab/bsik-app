#!/bin/bash

# current script path
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

# call a PHP script relative to the current script DIR with all arguments
php "$DIR/bsikc.php" "$@"

# print a new line and wait until user press a key
echo ""

#wait until user press a key
read -n1 -r -p "Press any key to exit..." key