<?php

declare(strict_types=1);

class DWIPSThermostaticController extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        // Profiles ////////////////////////////////////////////////////////////////////////////////////////////////////////

        $profilename = "DWIPS." . $this->Translate("HVACMode");
        if(!IPS_VariableProfileExists($profilename)){
            IPS_CreateVariableProfile($profilename, 1);
            IPS_SetVariableProfileValues($profilename, 0, 4, 1);
            IPS_SetVariableProfileAssociation($profilename, 0, $this->Translate("Automatic"), "Clock", -1);
            IPS_SetVariableProfileAssociation($profilename, 1, $this->Translate("Comfort"), "Presence-100", -1);
            IPS_SetVariableProfileAssociation($profilename, 2, $this->Translate("Standby"), "Presence-0", -1);
            IPS_SetVariableProfileAssociation($profilename, 3, $this->Translate("Economy"), "Moon", -1);
            IPS_SetVariableProfileAssociation($profilename, 4, $this->Translate("Building Protection"), "Warning", -1);
        }else{
            IPS_DeleteVariableProfile($profilename);
            IPS_CreateVariableProfile($profilename, 1);
            IPS_SetVariableProfileValues($profilename, 0, 4, 1);
            IPS_SetVariableProfileAssociation($profilename, 0, $this->Translate("Automatic"), "Clock", -1);
            IPS_SetVariableProfileAssociation($profilename, 1, $this->Translate("Comfort"), "Presence-100", -1);
            IPS_SetVariableProfileAssociation($profilename, 2, $this->Translate("Standby"), "Presence-0", -1);
            IPS_SetVariableProfileAssociation($profilename, 3, $this->Translate("Economy"), "Moon", -1);
            IPS_SetVariableProfileAssociation($profilename, 4, $this->Translate("Building Protection"), "Warning", -1);
            
        }

        $profilename = "DWIPS." . $this->Translate("OperationMode");
        if (!IPS_VariableProfileExists($profilename)) {
            IPS_CreateVariableProfile($profilename, 1);
            IPS_SetVariableProfileValues($profilename, 0, 2, 1);
            IPS_SetVariableProfileAssociation($profilename, 0, $this->Translate("Automatic"), "Clock", -1);
            IPS_SetVariableProfileAssociation($profilename, 1, $this->Translate("Heating"), "Flame", -1);
            IPS_SetVariableProfileAssociation($profilename, 2, $this->Translate("Cooling"), "Snowflake", -1);
        } else {
            IPS_DeleteVariableProfile($profilename);
            IPS_CreateVariableProfile($profilename, 1);
            IPS_SetVariableProfileValues($profilename, 0, 4, 1);
            IPS_SetVariableProfileAssociation($profilename, 0, $this->Translate("Automatic"), "Clock", -1);
            IPS_SetVariableProfileAssociation($profilename, 1, $this->Translate("Heating"), "Flame", -1);
            IPS_SetVariableProfileAssociation($profilename, 2, $this->Translate("Cooling"), "Snowflake", -1);

        }

        // Properties ////////////////////////////////////////////////////////////////////////////////////////////////////////

        $this->RegisterPropertyInteger("TargetTempVarID",0);
        $this->RegisterPropertyInteger("ActualTempVarID", 0);

        // Attributes ////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Variables ////////////////////////////////////////////////////////////////////////////////////////////////////////

        $this->MaintainVariable('OutputValue', $this->Translate('Output Value'), 1, '~Intensity.100', 1, true);

        $this->MaintainVariable('TargetTemp', $this->Translate('Target Temp'), 2, '~Temperature.Room', 2, true);
        $this->EnableAction('TargetTemp');

        $this->MaintainVariable('ActualTemp', $this->Translate('Actual Temp'), 2, '~Temperature.Room', 3, true);
        //$this->EnableAction('ActualTemp');

        $this->MaintainVariable('HVACMode', $this->Translate('HVAC Mode'), 1, "DWIPS." . $this->Translate("HVACMode"), 4, true);
        $this->EnableAction('HVACMode');

        $this->MaintainVariable('OperationMode', $this->Translate('Operation Mode'), 1, "DWIPS." . $this->Translate("OperationMode"), 4, true);
        $this->EnableAction('OperationMode');

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

        //Delete all registrations in order to readd them
        foreach ($this->GetMessageList() as $senderID => $messages) {
            foreach ($messages as $message) {
                $this->UnregisterMessage($senderID, $message);
            }
        }

        //Messages ////////////////////////////////////////////////////////////////////////////////////////////////////////

        $TargetTempVarID = $this->ReadPropertyInteger('TargetTempVarID');
        if (IPS_VariableExists($TargetTempVarID)) {
            $this->RegisterMessage($TargetTempVarID, VM_UPDATE);
        }

        $ActualTempVarID = $this->ReadPropertyInteger('ActualTempVarID');
        if (IPS_VariableExists($ActualTempVarID)) {
            $this->RegisterMessage($ActualTempVarID, VM_UPDATE);
        }
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        # IPS_LogMessage("MessageSink", "Message from SenderID ".$SenderID." with Message ".$Message."\r\n Data: ".print_r($Data, true));

        
        if (($Message == VM_UPDATE) and $SenderID == $this->ReadPropertyInteger('TargetTempVarID')) {
            $this->SetValue('TargetTemp', $Data[0]);
        }

        if (($Message == VM_UPDATE) and $SenderID == $this->ReadPropertyInteger('ActualTempVarID')) {
            $this->SetValue('ActualTemp', $Data[0]);
        }
    }

    public function RequestAction($Ident, $Value){
        switch ($Ident) {
            case 'TargetTemp':
                if (IPS_VariableExists($this->ReadPropertyInteger('TargetTempVarID'))) {
                    RequestAction($this->ReadPropertyInteger('TargetTempVarID'), $Value);                    
                }else{
                    $this->SetValue('TargetTemp', $Value);
                }
                $this->SendDebug("TargetTemp:", $Value, 0);
                break;

            case 'ActualTemp':
                if (IPS_VariableExists($this->ReadPropertyInteger('ActualTempVarID'))) {
                    RequestAction($this->ReadPropertyInteger('ActualTempVarID'), $Value);
                }else{
                    $this->SetValue('ActualTemp', $Value);
                }
                $this->SendDebug("ActualTemp:", $Value, 0);
                break;

            case 'HVACMode':
                $this->SetValue('HVACMode', $Value);
                $this->SendDebug("HVACMode:", $Value, 0);
                break;

            case 'OperationMode':
                $this->SetValue('OperationMode', $Value);
                $this->SendDebug("OperationMode:", $Value, 0);
                break;

            default:
                throw new Exception('Invalid ident');
        }
    }
}