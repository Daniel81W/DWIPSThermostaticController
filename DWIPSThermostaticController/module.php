<?php

declare(strict_types=1);

class DWIPSThermostaticController extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        // Properties
        $this->RegisterPropertyInteger("TargetTempVarID",0);
        $this->RegisterPropertyInteger("ActualTempVarID", 0);

        // Attributes

        // Variables

        $this->MaintainVariable('OutputValue', $this->Translate('Output Value'), 1, '~Intensity.100', 1, true);

        $this->MaintainVariable('TargetTemp', $this->Translate('Target Temp'), 2, '~Temperature', 2, true);
        $this->EnableAction('TargetTemp');

        $this->MaintainVariable('ActualTemp', $this->Translate('Actual Temp'), 2, '~Temperature', 3, true);
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