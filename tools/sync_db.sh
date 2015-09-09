wget http://tenbreaths.rbge.info/tools/tenbreaths.sql.tgz
tar zxvf tenbreaths.sql.tgz
rm tenbreaths.sql.tgz
mysql -u rogerhyam -p tenbreaths < tenbreaths.sql
