#/bin/bash
if [[ $EUID -ne 0 ]]; then
  echo "This script must be run as root" 
  exit 1
fi

rm -rf ~/website_tmp
git clone --depth=1 --branch=master https://github.com/Tjstretchalot/LoansBot-Site-New ~/website_tmp
rsync -avh --filter 'protect html/js/MathJax' --filter 'protect html/js/fontawesome-all.min.js' ~/website_tmp/html /var/www/ --delete
rsync -avh ~/website_tmp/includes /var/www/ --delete
rm -rf ~/website_tmp
