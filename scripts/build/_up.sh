# assign environment variable
APP_DIR=`readlink -f ./../../`

# cleanup session and cache data before deployment
./cleanup.sh

# application deployment
rsync -avz $APP_DIR/application/* nash:/home/nashmast/application/
rsync -avz $APP_DIR/data/* nash:/home/nashmast/data/
./_up_public.sh

# code libraries
rsync -avz $APP_DIR/library/Custom/* nash:/home/nashmast/library/Custom/
rsync -avz $APP_DIR/library/Custom/* nash:/home/nashmast/library/Joss/
rsync -avz $APP_DIR/library/Custom/* nash:/home/nashmast/library/Nashmaster/
rsync -uv $APP_DIR/library/simple_html_dom.php nash:/home/nashmast/library/simple_html_dom.php
rsync -avz /usr/share/php/libzend-framework-php/Zend/* nash:/home/nashmast/library/Zend/
rsync -avz /usr/share/php/libzend-framework-php/ZendX/* nash:/home/nashmast/library/ZendX/

# scripts 
rsync -uv $APP_DIR/scripts/zf-cli.php nash:/home/nashmast/scripts/zf-cli.php
