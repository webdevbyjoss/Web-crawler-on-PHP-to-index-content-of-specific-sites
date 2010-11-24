# assign environment variable
APP_DIR=`readlink -f ./../../`

# deploy public directory
rsync -avz $APP_DIR/public_html/* nash:/home/nashmast/public_html/
rsync -uv $APP_DIR/public_html/.htaccess nash:/home/nashmast/public_html/.htaccess

