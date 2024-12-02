<?php
/** @noinspection PhpExpressionResultUnusedInspection */

declare(strict_types=1);

require_once __DIR__ . '/../lib/aider/DWIPS_VariableProfileAider.php';
require_once __DIR__ . '/../lib/aider/DWIPS_VariableAider.php';

/** @noinspection PhpUnused */
class DWIPSThermostaticController extends IPSModule
{
    use DWIPS\Aider\DWIPS_VariableProfileAider;
    use DWIPS\Aider\DWIPS_VariableAider;

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        // Profiles ////////////////////////////////////////////////////////////////////////////////////////////////////////
        $this->UpdateVariableProfiles();

        // Properties ////////////////////////////////////////////////////////////////////////////////////////////////////////

        $this->RegisterPropertyInteger("TargetTempVarID",0);
        $this->RegisterPropertyInteger("ActualTempVarID", 0);
        $this->RegisterPropertyInteger("WindowStateVarID", 0);
        $this->RegisterPropertyInteger("HeatingSystemOperationModeVarID", 0);

        // Attributes ////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Variables ////////////////////////////////////////////////////////////////////////////////////////////////////////
        $this->CreateOrUpdateVariable('OutputValue', $this->Translate('Output Value'), VARIABLETYPE_INTEGER, '~Intensity.100');
        //$this->MaintainVariable('OutputValue', $this->Translate('Output Value'), VARIABLETYPE_INTEGER, '~Intensity.100', 1, true);

        $this->CreateOrUpdateVariable('TargetTemp', $this->Translate('Target Temp'), VARIABLETYPE_FLOAT, '~Temperature.Room', true);
        //$this->MaintainVariable('TargetTemp', $this->Translate('Target Temp'), VARIABLETYPE_FLOAT, '~Temperature.Room', 2, true);
        //$this->EnableAction('TargetTemp');

        $this->CreateOrUpdateVariable('ActualTemp', $this->Translate('Actual Temp'), VARIABLETYPE_FLOAT, '~Temperature.Room');
        //$this->MaintainVariable('ActualTemp', $this->Translate('Actual Temp'), VARIABLETYPE_FLOAT, '~Temperature.Room', 3, true);
        //$this->EnableAction('ActualTemp');

        $this->CreateOrUpdateVariable('HVACMode', $this->Translate('HVAC Mode'), VARIABLETYPE_INTEGER, "DWIPS." . $this->Translate("HVACMode"), true);
        //$this->MaintainVariable('HVACMode', $this->Translate('HVAC Mode'), VARIABLETYPE_INTEGER, "DWIPS." . $this->Translate("HVACMode"), 4, true);
        //$this->EnableAction('HVACMode');

        $this->CreateOrUpdateVariable('ForceHVACMode', $this->Translate('Force HVAC Mode'), VARIABLETYPE_BOOLEAN, "DWIPS." . $this->Translate("ForceMode"), true);
        //$this->MaintainVariable('ForceHVACMode', $this->Translate('Force HVAC Mode'), VARIABLETYPE_BOOLEAN, "DWIPS." . $this->Translate("ForceMode"), 5, true);
        //$this->EnableAction('ForceHVACMode');

        $this->CreateOrUpdateVariable('HVACModeState', $this->Translate('HVAC Mode State'), VARIABLETYPE_INTEGER, "DWIPS." . $this->Translate("HVACMode"));
        //$this->MaintainVariable('HVACModeState', $this->Translate('HVAC Mode State'), VARIABLETYPE_INTEGER, "DWIPS." . $this->Translate("HVACMode"), 6, true);

        $this->CreateOrUpdateVariable('OperationMode', $this->Translate('Operation Mode'), VARIABLETYPE_INTEGER, "DWIPS." . $this->Translate("OperationMode"), true);
        //$this->MaintainVariable('OperationMode', $this->Translate('Operation Mode'), VARIABLETYPE_INTEGER, "DWIPS." . $this->Translate("OperationMode"), 7, true);
        //$this->EnableAction('OperationMode');

        $this->CreateOrUpdateVariable('OperationModeState', $this->Translate('Operation Mode State'), VARIABLETYPE_INTEGER, "DWIPS." . $this->Translate("OperationMode"), true);
        $this->MaintainVariable('OperationModeState', $this->Translate('Operation Mode State'), VARIABLETYPE_INTEGER, "DWIPS." . $this->Translate("OperationMode"), 8, true);
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

        //Delete all message registrations
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

        $WindowStateVarID = $this->ReadPropertyInteger('WindowStateVarID');
        if (IPS_VariableExists($WindowStateVarID)) {
            $this->RegisterMessage($WindowStateVarID, VM_UPDATE);
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

        if (($Message == VM_UPDATE) and $SenderID == $this->ReadPropertyInteger('WindowStateVarID')) {
            $this->DetermineHVACMode();
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
                $this->DetermineHVACMode();
                break;

            case 'ForceHVACMode':
                $this->SetValue('ForceHVACMode', $Value);
                $this->SendDebug("ForceHVACMode:", $Value, 0);
                $this->DetermineHVACMode();
                break;

            case 'HVACModeState':
                $this->SetValue('HVACModeState', $Value);
                $this->SendDebug("HVACModeState:", $Value, 0);
                break;

            case 'OperationMode':
                $this->SetValue('OperationMode', $Value);
                $this->SendDebug("OperationMode:", $Value, 0);
                break;

            default:
                throw new Exception('Invalid ident');
        }
    }

    public function CalculateOutput(){
        if (IPS_VariableExists($this->ReadPropertyInteger('HeatingSystemOperationModeVarID'))) {
            if(GetValueBoolean($this->ReadPropertyInteger('HeatingSystemOperationModeVarID'))) {
                $mode = 4;
                //goto done;
            }
        }
    }

    public function DetermineHVACMode():void{

        if($this->GetValue('ForceHVACMode')){
            $mode = $this->GetValue("HVACMode");
            goto done;
        }

        //Window state
        if (IPS_VariableExists($this->ReadPropertyInteger('WindowStateVarID'))) {
            if(GetValueBoolean($this->ReadPropertyInteger('WindowStateVarID'))) {
                $mode = 4;
                goto done;
            }
        }

        $mode = $this->GetValue("HVACMode");

        done:
        $this->SetValue("HVACModeState", $mode);
    }

    private function UpdateVariableProfiles(){
        $this->MaintainVariableProfileAssoc(VARIABLETYPE_INTEGER, "DWIPS." . $this->Translate("HVACMode"), '', '', '', array(
            array(0, $this->Translate("Automatic"), "Clock", -1),
            array(1, $this->Translate("Comfort"), "Presence-100", -1),
            array(2, $this->Translate("Standby"), "Presence-0", -1),
            array(3, $this->Translate("Economy"), "Moon", -1),
            array(4, $this->Translate("Building Protection"), "Warning", -1),
            ),
            0,4,1
        );

        $this->MaintainVariableProfileAssoc(VARIABLETYPE_INTEGER, "DWIPS." . $this->Translate("OperationMode"), '', '', '', array(
            array(0, $this->Translate("Automatic"), "Clock", -1),
            array(1, $this->Translate("Heating"), "Flame", -1),
            array(2, $this->Translate("Cooling"), "Snowflake", -1),
            ),
            0,2,1
        );
        /*
        $profilename = "DWIPS." . $this->Translate("OperationMode");
        if (IPS_VariableProfileExists($profilename)) {
            IPS_DeleteVariableProfile($profilename);
        }
        IPS_CreateVariableProfile($profilename, 1);
        IPS_SetVariableProfileValues($profilename, 0, 2, 1);
        IPS_SetVariableProfileAssociation($profilename, 0, $this->Translate("Automatic"), "Clock", -1);
        IPS_SetVariableProfileAssociation($profilename, 1, $this->Translate("Heating"), "Flame", -1);
        IPS_SetVariableProfileAssociation($profilename, 2, $this->Translate("Cooling"), "Snowflake", -1);
*/
        $this->MaintainVariableProfileAssoc(VARIABLETYPE_BOOLEAN, "DWIPS." . $this->Translate("ForceMode"), '', '', '', array(
            array(0, $this->Translate("Normal"), "", -1),
            array(1, $this->Translate("Forced"), "", -1),
            )
        );
        /*
        $profilename = "DWIPS." . $this->Translate("ForceMode");
        if (IPS_VariableProfileExists($profilename)) {
            IPS_DeleteVariableProfile($profilename);
        }
        IPS_CreateVariableProfile($profilename, 0);
        IPS_SetVariableProfileAssociation($profilename, 0, $this->Translate("Normal"), "", -1);
        IPS_SetVariableProfileAssociation($profilename, 1, $this->Translate("Forced"), "", -1);
        */
        /*
                $profilename = "DWIPS." . $this->Translate("OperationModeState");
                if (IPS_VariableProfileExists($profilename)) {
                    IPS_DeleteVariableProfile($profilename);
                }
                IPS_CreateVariableProfile($profilename, 0);
                IPS_SetVariableProfileAssociation($profilename, 0, $this->Translate("Normal"), "", -1);
                IPS_SetVariableProfileAssociation($profilename, 1, $this->Translate("Forced"), "", -1);
        */
    }


}
