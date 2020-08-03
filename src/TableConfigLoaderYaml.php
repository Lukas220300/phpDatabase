<?php

namespace SCHOENBECK\Database;

use Symfony\Component\Yaml\Yaml;

class TableConfigLoaderYaml
{

    public static function loadConfigFromFile(string $fileName)
    {
        return Yaml::parseFile($fileName);
    }

}