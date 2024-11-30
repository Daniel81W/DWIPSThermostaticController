<?php

declare(strict_types=1);

class DWIPSThermostaticController extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        // Profiles
        $profilename = "DWIPS" . $this->Translate("HVACMode");
        if(!IPS_VariableProfileExists($profilename)){
            IPS_CreateVariableProfile($profilename, 1);
            IPS_SetVariableProfileValues($profilename, 0, 4, 1);
            IPS_SetVariableProfileAssociation($profilename, 0, $this->Translate("Automatic"), "Clock", -1);
            IPS_SetVariableProfileAssociation($profilename, 1, $this->Translate("Comfort"), "Presence-100", -1);
            IPS_SetVariableProfileAssociation($profilename, 2, $this->Translate("Standby"), "Presence-0", -1);
            IPS_SetVariableProfileAssociation($profilename, 3, $this->Translate("Economy"), "Moon", -1);
            IPS_SetVariableProfileAssociation($profilename, 4, $this->Translate("Building Protection"), "Warning", -1);
        }
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

        $this->MaintainVariable('HVACMode', $this->Translate('HVAC Mode'), 1, "DWIPS" . $this->Translate("HVACMode"), 4, true);
        $this->EnableAction('HVACMode');
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

    public function RequestAction(string $Ident, string $Value){
        switch ($Ident) {
            case 'HVACMode':
                $this->SetValue('HVACMode', $Value);
                $this->SendDebug("HVACMode:", $Value, 0);
                break;

            default:
                throw new Exception('Invalid ident');
        }
    }
}