cp ../original/*.png .
mogrify -bordercolor transparent -border 25 *.png
mogrify -resize 32x32 *.png
