<?php
function image_handler($source_image,$destination,$tn_w = 100,$tn_h = 100,$quality = 80,$wmsource = false) {

  // Die getimagesize-Funktion stellt eine "imagetype"-String-Konstante bereit, die an die Funktion image_type_to_mime_type für den entsprechenden Mime-Typ übergeben werden kann
  $info = getimagesize($source_image);
  $imgtype = image_type_to_mime_type($info[2]);

  // Dann kann der Mime-Typ verwendet werden, um die richtige Funktion aufzurufen, um eine Bildressource aus dem bereitgestellten Bild zu generieren
  switch ($imgtype) {
  case 'image/jpeg':
    $source = imagecreatefromjpeg($source_image);
    break;
  case 'image/gif':
    $source = imagecreatefromgif($source_image);
    break;
  case 'image/png':
    $source = imagecreatefrompng($source_image);
    break;
  default:
    die('Invalid image type.');
  }

  // Jetzt können wir die Abmessungen des bereitgestellten Bildes bestimmen und das Breiten-/Höhenverhältnis berechnen
  $src_w = imagesx($source);
  $src_h = imagesy($source);
  $src_ratio = $src_w/$src_h;

  // Jetzt können wir die Macht der Mathematik nutzen, um zu bestimmen, ob das Bild auf die neuen Abmessungen zugeschnitten werden muss, und wenn ja, ob es vertikal oder horizontal zugeschnitten werden soll. Wir werden nur aus der Mitte zuschneiden, um dies einfach zu halten.
  if ($tn_w/$tn_h > $src_ratio) {
  $new_h = $tn_w/$src_ratio;
  $new_w = $tn_w;
  } else {
  $new_w = $tn_h*$src_ratio;
  $new_h = $tn_h;
  }
  $x_mid = $new_w/2;
  $y_mid = $new_h/2;

  // Wenden Sie nun tatsächlich den Zuschnitt an und ändern Sie die Größe!
  $newpic = imagecreatetruecolor(round($new_w), round($new_h));
  imagecopyresampled($newpic, $source, 0, 0, 0, 0, $new_w, $new_h, $src_w, $src_h);
  $final = imagecreatetruecolor($tn_w, $tn_h);
  imagecopyresampled($final, $newpic, 0, 0, ($x_mid-($tn_w/2)), ($y_mid-($tn_h/2)), $tn_w, $tn_h, $tn_w, $tn_h);

  // Wenn eine Wasserzeichenquelldatei angegeben ist, rufen Sie auch die Informationen zum Wasserzeichen ab. Dies ist das gleiche, was wir oben für das Quellbild gemacht haben.
  if($wmsource) {
  $info = getimagesize($wmsource);
  $imgtype = image_type_to_mime_type($info[2]);
  switch ($imgtype) {
    case 'image/jpeg':
      $watermark = imagecreatefromjpeg($wmsource);
      break;
    case 'image/gif':
      $watermark = imagecreatefromgif($wmsource);
      break;
    case 'image/png':
      $watermark = imagecreatefrompng($wmsource);
      break;
    default:
      die('Invalid watermark type.');
  }

  // Bestimmen Sie die Größe des Wasserzeichens, da wir die Platzierung von der oberen linken Ecke des Wasserzeichenbilds aus angeben, sodass Breite und Höhe des Wasserzeichens eine Rolle spielen.
  $wm_w = imagesx($watermark);
  $wm_h = imagesy($watermark);

  // Berechnen Sie nun die Werte, um das Wasserzeichen in der unteren rechten Ecke zu platzieren. Sie können eine oder beide Variablen auf "0" setzen, um die gegenüberliegenden Ecken mit einem Wasserzeichen zu versehen, oder Ihre eigenen Berechnungen durchführen, um sie an einer anderen Stelle zu platzieren.
  $wm_x = $tn_w - $wm_w;
  $wm_y = $tn_h - $wm_h;

  // Kopieren Sie das Wasserzeichen auf das Originalbild
  // Die letzten 4 Argumente bedeuten nur, das gesamte Wasserzeichen zu kopieren
  imagecopy($final, $watermark, $wm_x, $wm_y, 0, 0, $tn_w, $tn_h);
  }

  // Ok, speichern Sie die Ausgabe als JPEG im angegebenen Zielpfad in der gewünschten Qualität.
  // Sie könnten hier imagepng oder imagegif verwenden, wenn Sie stattdessen diese Dateitypen ausgeben möchten.
  if(Imagejpeg($final,$destination,$quality)) {
  return true;
  }

  // Wenn etwas schief gelaufen ist
  return false;

}
?>

<!-- Füge ein kleines Bild-Upload-Formular hinzu -->
<form method="post" enctype="multipart/form-data">
Source Image: <input type="file" name="uploaded_image" />
<input type="submit" value="Handle This Image" />
</form>

<?php
//Verarbeiten Sie die Formularübermittlung
if($_FILES) {
  //Holen Sie sich das hochgeladene Bild
  $source_image = $_FILES['uploaded_image']['tmp_name'];

  //Geben Sie den Ausgabepfad in Ihrem Dateisystem und die Bildgröße/-qualität an
  $destination = '/path/to/the/final/image/filename.jpg';
  $tn_w = 400;
  $tn_h = 400;
  $quality = 100;

  //Pfad zu einem optionalen Wasserzeichen
  $wmsource = '/path/to/your/watermark/image.png';

  // Versuchen Sie, das Bild zu verarbeiten, und geben Sie eine kleine Meldung aus, unabhängig davon, ob es funktioniert hat oder nicht. Wenn das Bild an einem öffentlichen Ort gespeichert ist, können Sie ein <img src>-Tag hinzufügen, um das Bild auch hier anzuzeigen!
  $success = image_handler($source_image,$destination,$tn_w,$tn_h,$quality,$wmsource);
  if($success) { echo "Your image was saved successfully!"; }
  else { echo "Your image was not saved."; }
}

?>