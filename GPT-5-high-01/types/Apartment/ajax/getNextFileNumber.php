<?php

// Returns the next 4-digit files number for Apartment
$nr = DataManager::nextApartmentFilesNr();
echo json_encode([ 'ok' => true, 'number' => $nr ]);
