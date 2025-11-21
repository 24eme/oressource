<?php

/*
  Oressource
  Copyright (C) 2014-2018  Martin Vert and Oressource devellopers

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Affero General Public License as
  published by the Free Software Foundation, either version 3 of the
  License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Affero General Public License for more details.

  You should have received a copy of the GNU Affero General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once('../core/session.php');
require_once('../core/requetes.php');

/**
 * Fonction qui permet de telecharger un fichier via son nom,
 *
 * Documentation des types mimes sur MDN:
 * https://developer.mozilla.org/fr/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Complete_list_of_MIME_type
 * @param string $pathToFile
 * @param string $type type MIME complet
 * @param string $attachementName Nom du fichier à télécharger par defaut vide.
 */

if (is_valid_session() && is_allowed_config()) {
  require_once('../moteur/dbconfig.php');

  # Get the ressourcerie name provided in database
  $struct = structure($bdd)['nom'];

  # Go into the ./mysql folder
  $exportPath = '../mysql/';
  chdir($exportPath);

  # Name the sql dump file
  $exportFileName = 'export_oressource';
  $fileExtention = '.sql';
  $exportPathServer = $exportFileName . $fileExtention;

  # Dump the database via mysqldump (provided by mysql-client)
  $worked = exec("mysqldump --opt --host=$host --user=$user --password=$pass $base > \"$exportPathServer\"");

  // Remove spaces from name and name the zip file
  $struct = strtolower(str_replace(" ", "_", $struct));
  $fileZip = $exportFileName . '_' . $struct . '_' . date("d-m-Y") . '.zip';

  $zip = new ZipArchive();
  if ($zip->open($fileZip, ZipArchive::CREATE)!== TRUE) {
    header("Location:structures.php?err=Probleme pendant le zippage du fichier");
    exit;
  }
  $zip->addFile($exportPathServer, $struct . '_' . date("d-m-Y").'.sql');
  $zip->close();

  // Delete sql file
  unlink($exportPathServer);

  header("Content-Type: application/zip");
  header("Content-disposition: attachment; filename=".$fileZip);
  header("Content-Length: $size");
  echo file_get_contents($fileZip);
  unlink($fileZip);
} else {
  header('Location:../moteur/destroy.php');
}
