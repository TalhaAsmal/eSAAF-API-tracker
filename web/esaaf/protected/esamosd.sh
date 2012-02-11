cd /var/www/html/esamosd/citizenPages/
wget -q -O- api.erepublik.com/v2/feeds/countries/51/citizens.xml.gz | gunzip > esa.xml
