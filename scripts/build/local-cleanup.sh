APP_DIR=`readlink -f ./../../`

# remove session data
rm -f $APP_DIR/data/session/*

# remove cache data
#rm -f $APP_DIR/data/cache/*

# remove tmp cache
rm -rf $APP_DIR/tmp/zend*
