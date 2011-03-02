# assign environment variable
APP_DIR=`readlink -f ./../../`

# cleanup session and cache data before deployment
./local-cleanup.sh

# application deployment
# NOTE: we skipping configuration directory
rsync -avz $APP_DIR/application/controllers/* nash:/home/nashmast/application/controllers/
rsync -avz $APP_DIR/application/layouts/* nash:/home/nashmast/application/layouts/
rsync -avz $APP_DIR/application/models/* nash:/home/nashmast/application/models/
rsync -avz $APP_DIR/application/modules/* nash:/home/nashmast/application/modules/
rsync -avz $APP_DIR/application/services/* nash:/home/nashmast/application/services/
rsync -avz $APP_DIR/application/views/* nash:/home/nashmast/application/views/
rsync -avz $APP_DIR/application/Bootstrap.php nash:/home/nashmast/application/Bootstrap.php

# internal application resources deployment
rsync -avz $APP_DIR/data/* nash:/home/nashmast/data/
./_up_public.sh

# code libraries
rsync -avz $APP_DIR/library/Custom/* nash:/home/nashmast/library/Custom/
rsync -avz $APP_DIR/library/Joss/* nash:/home/nashmast/library/Joss/
rsync -avz $APP_DIR/library/Nashmaster/* nash:/home/nashmast/library/Nashmaster/
rsync -uv $APP_DIR/library/simple_html_dom.php nash:/home/nashmast/library/simple_html_dom.php
rsync -avz /var/www/zend/Zend/* nash:/home/nashmast/library/Zend/
rsync -avz /var/www/zend/ZendX/* nash:/home/nashmast/library/ZendX/

# scripts 
rsync -uv $APP_DIR/scripts/zf-cli.php nash:/home/nashmast/scripts/zf-cli.php

# force cache rebuild on production after update
./remove-cache-on-production.sh
