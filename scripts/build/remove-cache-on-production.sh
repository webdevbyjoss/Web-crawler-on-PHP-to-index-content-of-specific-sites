echo "forcing cache rebuild on production..."
ssh nash "rm -rf ./tmp/zend*;exit;";
echo "done";
