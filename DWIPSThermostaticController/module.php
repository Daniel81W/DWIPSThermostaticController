<?php

declare(strict_types=1);

class DWIPSThermostaticController extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        // Properties

        // Attributes

        // Variables

        $this->MaintainVariableInteger('OutputValue', $this->Translate('Output Value'), '~Intensity.100', 1);

        $this->MaintainVariableFloat('TargetTemp', $this->Translate('Target Temp'), '~Temperature', 2);
        $this->EnableAction('TargetTemp');

        $this->MaintainVariableFloat('ActualTemp', $this->Translate('Actual Temp'), '~Temperature', 3);
        $this->EnableAction('ActualTemp');
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
    }
}