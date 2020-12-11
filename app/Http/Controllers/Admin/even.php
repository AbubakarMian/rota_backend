
<?php

$numbers = array();

for ($i=0; $i<=1000; $i++) {
   $numbers[]=mt_rand(1,1000);
   if ($i % 2 == 0){
    $even[]=$i;
  } else {
    $odd[]=$i;
  }
}

echo $even;
echo $odd;

?>
