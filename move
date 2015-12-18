if [ "$1" == "" ]; then
	echo "use $0 package  [ from [ to ]]"
	exit 1
fi
rpm=$1
from=${2-test}
to=${3-remi}

for i in fedora/??/$from/*/$rpm* enterprise/?/$from/*/$rpm*
do
  if [ -f $i ]
  then
    j=$(echo $i | sed -e "s:/$from/:/$to/:")
    ln $i $j && basename $i
  fi
done
