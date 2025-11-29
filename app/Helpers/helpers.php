<?php

if (! function_exists('deployment_id')) {
    function deployment_id(): string
    {
        return \App\Helpers\GitHelper::getDeploymentId();
    }
}
