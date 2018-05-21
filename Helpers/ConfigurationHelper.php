<?php

namespace Phobetor\RabbitMqSupervisorBundle\Helpers;

class ConfigurationHelper
{
    public function getConfigurationStringFromDataArray(array $data)
    {
        $configurationString = '';

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $configurationString .= sprintf("[%s]\n", $key);
                $configurationString .= $this->getConfigurationStringFromDataArray($value);
                $configurationString .= "\n";
            } else {
                $configurationString .= sprintf("%s=%s\n", $key, $value);
            }
        }

        return $configurationString;
    }
}
