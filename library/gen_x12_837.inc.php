<?php
// Copyright (C) 2007-2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("Claim.class.php");

function gen_x12_837($pid, $encounter, &$log, $encounter_claim=false) {

  $today = time();
  $out = '';
  $claim = new Claim($pid, $encounter);
  $edicount = 0;

  $log .= "Generating claim $pid-$encounter for " .
    $claim->patientFirstName()  . ' ' .
    $claim->patientMiddleName() . ' ' .
    $claim->patientLastName()   . ' on ' .
    date('Y-m-d H:i', $today) . ".\n";

  $out .= "ISA" .
    "*00" .
    "*          " .
    "*00" .
    "*          " .
    "*" . $claim->x12gsisa05() .
    "*" . $claim->x12gssenderid() .
    "*" . $claim->x12gsisa07() .
    "*" . $claim->x12gsreceiverid() .
    "*030911" .
    "*1630" .
    "*U" .
    "*00401" .
    "*000000001" .
    "*" . $claim->x12gsisa14() .
    "*" . $claim->x12gsisa15() .
    "*:" .
    "~\n";

  $out .= "GS" .
    "*HC" .
    "*" . $claim->x12gsgs02() .
    "*" . trim($claim->x12gsreceiverid()) .
    "*" . date('Ymd', $today) .
    "*" . date('Hi', $today) .
    "*1" .
    "*X" .
    "*" . $claim->x12gsversionstring() .
    "~\n";

  ++$edicount;
  $out .= "ST" .
    "*837" .
    "*0021" .
    "~\n";

  ++$edicount;
  $out .= "BHT" .
    "*0019" .
    "*00" .
    "*0123" .
    "*" . date('Ymd', $today) .
    "*1023" .
    ($encounter_claim ? "*RP" : "*CH") .
    "~\n";

  ++$edicount;
  $out .= "REF" .
    "*87" .
    "*" . $claim->x12gsversionstring() .
    "~\n";

  ++$edicount;
  $out .= "NM1" .       // Loop 1000A Submitter
    "*41" .
    "*2" .
    "*" . $claim->billingFacilityName() .
    "*" .
    "*" .
    "*" .
    "*" .
    "*46";
   if (trim($claim->x12gsreceiverid()) == '470819582') { // if ECLAIMS EDI
    $out  .=  "*" . $claim->clearingHouseETIN();
   } else {
    $out  .=  "*" . $claim->billingFacilityETIN();
   }
    $out .= "~\n";

  ++$edicount;
  $out .= "PER" .
    "*IC" .
    "*" . $claim->billingContactName() .
    "*TE" .
    "*" . $claim->billingContactPhone();
  if ($claim->x12gsper06()) {
    $out .= "*ED*" . $claim->x12gsper06();
  }
  $out .= "~\n";

  ++$edicount;
  $out .= "NM1" .       // Loop 1000B Receiver
    "*40" .
    "*2" .
    "*" . $claim->clearingHouseName() .
    "*" .
    "*" .
    "*" .
    "*" .
    "*46" .
    "*" . $claim->clearingHouseETIN() .
    "~\n";

  $HLcount = 1;

  ++$edicount;
  $out .= "HL" .        // Loop 2000A Billing/Pay-To Provider HL Loop
    "*$HLcount" .
    "*" .
    "*20" .
    "*1" .
    "~\n";

  $HLBillingPayToProvider = $HLcount++;

  ++$edicount;
  $out .= "NM1" .       // Loop 2010AA Billing Provider
    "*85" .
    "*2" .
    "*" . $claim->billingFacilityName() .
    "*" .
    "*" .
    "*" .
    "*";
  if ($claim->billingFacilityNPI()) {
    $out .= "*XX*" . $claim->billingFacilityNPI();
  } else {
    $log .= "*** Billing facility has no NPI.\n";
    $out .= "*24*" . $claim->billingFacilityETIN();
  }
  $out .= "~\n";

  ++$edicount;
  $out .= "N3" .
    "*" . $claim->billingFacilityStreet() .
    "~\n";

  ++$edicount;
  $out .= "N4" .
    "*" . $claim->billingFacilityCity() .
    "*" . $claim->billingFacilityState() .
    "*" . $claim->billingFacilityZip() .
    "~\n";

  // Add a REF*EI*<ein> segment if NPI was specified in the NM1 above.
  if ($claim->billingFacilityNPI() && $claim->billingFacilityETIN()) {
    ++$edicount;
    $out .= "REF" ;
	if($claim->federalIdType()){
      $out .= "*" . $claim->federalIdType();
	}
	else{
	  $out .= "*EI";//For dealing with the situation before adding selection for TaxId type In facility ie default to EIN.
	}
      $out .=  "*" . $claim->billingFacilityETIN() .
      "~\n";
  }

  if ($claim->providerNumberType() && $claim->providerNumber()) {
    ++$edicount;
    $out .= "REF" .
      "*" . $claim->providerNumberType() .
      "*" . $claim->providerNumber() .
      "~\n";
  }
  else if ($claim->providerNumber()) {
    $log .= "*** Payer-specific provider insurance number is present but has no type assigned.\n";
  }

  ++$edicount;
  $out .= "NM1" .       // Loop 2010AB Pay-To Provider
    "*87" .
    "*2" .
    "*" . $claim->billingFacilityName() .
    "*" .
    "*" .
    "*" .
    "*";
  if ($claim->billingFacilityNPI())
    $out .= "*XX*" . $claim->billingFacilityNPI();
  else
    $out .= "*24*" . $claim->billingFacilityETIN();
  $out .= "~\n";

  ++$edicount;
  $out .= "N3" .
    "*" . $claim->billingFacilityStreet() .
    "~\n";

  ++$edicount;
  $out .= "N4" .
    "*" . $claim->billingFacilityCity() .
    "*" . $claim->billingFacilityState() .
    "*" . $claim->billingFacilityZip() .
    "~\n";

  if ($claim->billingFacilityNPI() && $claim->billingFacilityETIN()) {
    ++$edicount;
    $out .= "REF" .
      "*EI" .
      "*" . $claim->billingFacilityETIN() .
      "~\n";
  }

  $PatientHL = 0;

  ++$edicount;
  $out .= "HL" .        // Loop 2000B Subscriber HL Loop
    "*$HLcount" .
    "*$HLBillingPayToProvider" .
    "*22" .
    "*$PatientHL" .
    "~\n";

  $HLSubscriber = $HLcount++;

  if (!$claim->payerSequence()) {
    $log .= "*** Error: Insurance information is missing!\n";
  }
  ++$edicount;
  $out .= "SBR" .       // Subscriber Information
    "*" . $claim->payerSequence() .
    "*" . $claim->insuredRelationship() .
    "*" . $claim->groupNumber() .
    "*" . $claim->groupName() .
    "*" . $claim->insuredTypeCode() . // applies for secondary medicare
    "*" .
    "*" .
    "*" .
    "*" . $claim->claimType() . // Zirmed replaces this
    "~\n";

  ++$edicount;
  $out .= "NM1" .       // Loop 2010BA Subscriber
    "*IL" .
    "*1" .
    "*" . $claim->insuredLastName() .
    "*" . $claim->insuredFirstName() .
    "*" . $claim->insuredMiddleName() .
    "*" .
    "*" .
    "*MI" .
    "*" . $claim->policyNumber() .
    "~\n";

  ++$edicount;
  $out .= "N3" .
    "*" . $claim->insuredStreet() .
    "~\n";

  ++$edicount;
  $out .= "N4" .
    "*" . $claim->insuredCity() .
    "*" . $claim->insuredState() .
    "*" . $claim->insuredZip() .
    "~\n";

  ++$edicount;
  $out .= "DMG" .
    "*D8" .
    "*" . $claim->insuredDOB() .
    "*" . $claim->insuredSex() .
    "~\n";

  ++$edicount;
  $out .= "NM1" .       // Loop 2010BB Payer
    "*PR" .
    "*2" .
    "*" . $claim->payerName() .
    "*" .
    "*" .
    "*" .
    "*" .
    "*PI" .
    // Zirmed ignores this if using payer name matching:
    "*" . ($encounter_claim ? $claim->payerAltID() : $claim->payerID()) .
    "~\n";

  // if (!$claim->payerID()) {
  //   $log .= "*** CMS ID is missing for payer '" . $claim->payerName() . "'.\n";
  // }

  ++$edicount;
  $out .= "N3" .
    "*" . $claim->payerStreet() .
    "~\n";

  ++$edicount;
  $out .= "N4" .
    "*" . $claim->payerCity() .
    "*" . $claim->payerState() .
    "*" . $claim->payerZip() .
    "~\n";

  if (! $claim->isSelfOfInsured()) {
    ++$edicount;
    $out .= "HL" .        // Loop 2000C Patient Information
      "*$HLcount" .
      "*$HLSubscriber" .
      "*23" .
      "*0" .
      "~\n";

    $HLcount++;

    ++$edicount;
    $out .= "PAT" .
      "*" . $claim->insuredRelationship() .
      "~\n";

    ++$edicount;
    $out .= "NM1" .       // Loop 2010CA Patient
      "*QC" .
      "*1" .
      "*" . $claim->patientLastName() .
      "*" . $claim->patientFirstName() .
      "*" . $claim->patientMiddleName() .
      "~\n";

    ++$edicount;
    $out .= "N3" .
      "*" . $claim->patientStreet() .
      "~\n";

    ++$edicount;
    $out .= "N4" .
      "*" . $claim->patientCity() .
      "*" . $claim->patientState() .
      "*" . $claim->patientZip() .
      "~\n";

    ++$edicount;
    $out .= "DMG" .
      "*D8" .
      "*" . $claim->patientDOB() .
      "*" . $claim->patientSex() .
      "~\n";
  } // end of patient different from insured

  $proccount = $claim->procCount();

  $clm_total_charges = 0;
  for ($prockey = 0; $prockey < $proccount; ++$prockey) {
    $clm_total_charges += $claim->cptCharges($prockey);
  }

  if (!$clm_total_charges) {
    $log .= "*** This claim has no charges!\n";
  }


  ++$edicount;
  $out .= "CLM" .       // Loop 2300 Claim
    "*$pid-$encounter" .
    "*"  . sprintf("%.2f",$clm_total_charges) . // Zirmed computes and replaces this
    "*"  .
    "*"  .
    "*" . sprintf('%02d', $claim->facilityPOS()) . "::" . $claim->frequencyTypeCode() . // Changed to correct single digit output
    "*Y" .
    "*A" .
    "*"  . ($claim->billingFacilityAssignment() ? 'Y' : 'N') .
    "*Y" .
    "*C" .
    "~\n"; 
    
  ++$edicount;
  $out .= "DTP" .       // Date of Onset
    "*431" .
    "*D8" .
    "*" . $claim->onsetDate() .
    "~\n";

  if (strcmp($claim->facilityPOS(),'21') == 0) {
    ++$edicount;
    $out .= "DTP" .     // Date of Hospitalization
      "*435" .
      "*D8" .
      "*" . $claim->onsetDate() .
      "~\n";
  }

  $patientpaid = $claim->patientPaidAmount();
  if ($patientpaid != 0) {
    ++$edicount;
    $out .= "AMT" .     // Patient paid amount. Page 220.
      "*F5" .
      "*" . $patientpaid .
      "~\n";
  }

  if ($claim->priorAuth()) {
    ++$edicount;
    $out .= "REF" .     // Prior Authorization Number
      "*G1" .
      "*" . $claim->priorAuth() .
      "~\n";
  }

  if ($claim->cliaCode() and $claim->claimType() === 'MB') {
    // Required by Medicare when in-house labs are done.
    ++$edicount;
    $out .= "REF" .     // Clinical Laboratory Improvement Amendment Number
      "*X4" .
      "*" . $claim->cliaCode() .
      "~\n";
  }

  // Note: This would be the place to implement the NTE segment for loop 2300.

  // Diagnoses, up to 8 per HI segment.
  $da = $claim->diagArray();
  $diag_type_code = 'BK';
  $tmp = 0;
  foreach ($da as $diag) {
    if ($tmp % 8 == 0) {
      if ($tmp) $out .= "~\n";
      ++$edicount;
      $out .= "HI";         // Health Diagnosis Codes
    }
    $out .= "*$diag_type_code:" . $diag;
    $diag_type_code = 'BF';
    ++$tmp;
  }
  if ($tmp) $out .= "~\n";

  if ($claim->referrerLastName()) {
    // Medicare requires referring provider's name and UPIN.
    ++$edicount;
    $out .= "NM1" .     // Loop 2310A Referring Provider
      "*DN" .
      "*1" .
      "*" . $claim->referrerLastName() .
      "*" . $claim->referrerFirstName() .
      "*" . $claim->referrerMiddleName() .
      "*" .
      "*";
    if ($claim->referrerNPI()) { $out .=
      "*XX" .
      "*" . $claim->referrerNPI();
    } else { $out .=
      "*34" .
      "*" . $claim->referrerSSN();
    }
    $out .= "~\n";

    if ($claim->referrerTaxonomy()) {
      ++$edicount;
      $out .= "PRV" .
        "*RF" . // ReFerring provider
        "*ZZ" .
        "*" . $claim->referrerTaxonomy() .
        "~\n";
    }

    if ($claim->referrerUPIN()) {
      ++$edicount;
      $out .= "REF" .   // Referring Provider Secondary Identification
        "*1G" .
        "*" . $claim->referrerUPIN() .
        "~\n";
    }
  }

  ++$edicount;
  $out .= "NM1" .       // Loop 2310B Rendering Provider
    "*82" .
    "*1" .
    "*" . $claim->providerLastName() .
    "*" . $claim->providerFirstName() .
    "*" . $claim->providerMiddleName() .
    "*" .
    "*";
  if ($claim->providerNPI()) { $out .=
    "*XX" .
    "*" . $claim->providerNPI();
  } else { $out .=
    "*34" .
    "*" . $claim->providerSSN();
    $log .= "*** Rendering provider has no NPI.\n";
  }
  $out .= "~\n";

  if ($claim->providerTaxonomy()) {
    ++$edicount;
    $out .= "PRV" .
      "*PE" . // PErforming provider
      "*ZZ" .
      "*" . $claim->providerTaxonomy() .
      "~\n";
  }

  // REF*1C is required here for the Medicare provider number if NPI was
  // specified in NM109.  Not sure if other payers require anything here.
  // --- apparently ECLAIMS, INC wants the data in 2010 but NOT in 2310B - tony@mi-squared.com

   if (trim($claim->x12gsreceiverid()) != '470819582') { // if NOT ECLAIMS EDI
      if ($claim->providerNumber()) {
        ++$edicount;
        $out .= "REF" .
          "*" . $claim->providerNumberType() .
          "*" . $claim->providerNumber() .
          "~\n";
      }
   }

  // Loop 2310D is omitted in the case of home visits (POS=12).
  if ($claim->facilityPOS() != 12) {
    ++$edicount;
    $out .= "NM1" .       // Loop 2310D Service Location
      "*77" .
      "*2";
    if ($claim->facilityName() || $claim->facilityNPI() || $claim->facilityETIN()) { $out .=
      "*" . $claim->facilityName();
    }
    if ($claim->facilityNPI() || $claim->facilityETIN()) { $out .=
      "*" .
      "*" .
      "*" .
      "*";
      if ($claim->facilityNPI()) { $out .=
        "*XX*" . $claim->facilityNPI();
      } else { $out .=
        "*24*" . $claim->facilityETIN();
        $log .= "*** Service location has no NPI.\n";
      }
    }
    $out .= "~\n";
    if ($claim->facilityStreet()) {
      ++$edicount;
      $out .= "N3" .
        "*" . $claim->facilityStreet() .
        "~\n";
    }
    if ($claim->facilityState()) {
      ++$edicount;
      $out .= "N4" .
        "*" . $claim->facilityCity() .
        "*" . $claim->facilityState() .
        "*" . $claim->facilityZip() .
        "~\n";
    }
  }

  // Loop 2310E, Supervising Provider
  //
  if ($claim->supervisorLastName()) {
    ++$edicount;
    $out .= "NM1" .
      "*DQ" . // Supervising Physician
      "*1" .  // Person
      "*" . $claim->supervisorLastName() .
      "*" . $claim->supervisorFirstName() .
      "*" . $claim->supervisorMiddleName() .
      "*" .   // NM106 not used
      "*";    // Name Suffix
    if ($claim->supervisorNPI()) { $out .=
      "*XX" .
      "*" . $claim->supervisorNPI();
    } else { $out .=
      "*34" .
      "*" . $claim->supervisorSSN();
    }
    $out .= "~\n";

    if ($claim->supervisorNumber()) {
      ++$edicount;
      $out .= "REF" .
        "*" . $claim->supervisorNumberType() .
        "*" . $claim->supervisorNumber() .
        "~\n";
    }
  }

  $prev_pt_resp = $clm_total_charges; // for computation below

  // Loops 2320 and 2330*, other subscriber/payer information.
  //
  for ($ins = 1; $ins < $claim->payerCount(); ++$ins) {

    $tmp1 = $claim->claimType($ins);
    $tmp2 = 'C1'; // Here a kludge. See page 321.
    if ($tmp1 === 'CI') $tmp2 = 'C1';
    if ($tmp1 === 'AM') $tmp2 = 'AP';
    if ($tmp1 === 'HM') $tmp2 = 'HM';
    if ($tmp1 === 'MB') $tmp2 = 'MB';
    if ($tmp1 === 'MC') $tmp2 = 'MC';
    if ($tmp1 === '09') $tmp2 = 'PP';
    ++$edicount;
    $out .= "SBR" . // Loop 2320, Subscriber Information - page 318
      "*" . $claim->payerSequence($ins) .
      "*" . $claim->insuredRelationship($ins) .
      "*" . $claim->groupNumber($ins) .
      "*" . $claim->groupName($ins) .
      "*" . $tmp2 .
      "*" .
      "*" .
      "*" .
      "*" . $claim->claimType($ins) .
      "~\n";

    // Things that apply only to previous payers, not future payers.
    //
    if ($claim->payerSequence($ins) < $claim->payerSequence()) {

      // Generate claim-level adjustments.
      $aarr = $claim->payerAdjustments($ins);
      foreach ($aarr as $a) {
        ++$edicount;
        $out .= "CAS" . // Previous payer's claim-level adjustments. Page 323.
          "*" . $a[1] .
          "*" . $a[2] .
          "*" . $a[3] .
          "~\n";
      }

      $payerpaid = $claim->payerTotals($ins);
      ++$edicount;
      $out .= "AMT" . // Previous payer's paid amount. Page 332.
        "*D" .
        "*" . $payerpaid[1] .
        "~\n";

      // Patient responsibility amount as of this previous payer.
      $prev_pt_resp -= $payerpaid[1]; // reduce by payments
      $prev_pt_resp -= $payerpaid[2]; // reduce by adjustments

      ++$edicount;
      $out .= "AMT" . // Allowed amount per previous payer. Page 334.
        "*B6" .
        "*" . sprintf('%.2f', $payerpaid[1] + $prev_pt_resp) .
        "~\n";

      ++$edicount;
      $out .= "AMT" . // Patient responsibility amount per previous payer. Page 335.
        "*F2" .
        "*" . sprintf('%.2f', $prev_pt_resp) .
        "~\n";

    } // End of things that apply only to previous payers.

    ++$edicount;
    $out .= "DMG" . // Other subscriber demographic information. Page 342.
      "*D8" .
      "*" . $claim->insuredDOB($ins) .
      "*" . $claim->insuredSex($ins) .
      "~\n";

    ++$edicount;
    $out .= "OI" .  // Other Insurance Coverage Information. Page 344.
      "*" .
      "*" .
      "*Y" .
      "*B" .
      "*" .
      "*Y" .
      "~\n";

    ++$edicount;
    $out .= "NM1" . // Loop 2330A Subscriber info for other insco. Page 350.
      "*IL" .
      "*1" .
      "*" . $claim->insuredLastName($ins) .
      "*" . $claim->insuredFirstName($ins) .
      "*" . $claim->insuredMiddleName($ins) .
      "*" .
      "*" .
      "*MI" .
      "*" . $claim->policyNumber($ins) .
      "~\n";

    ++$edicount;
    $out .= "N3" .
      "*" . $claim->insuredStreet($ins) .
      "~\n";

    ++$edicount;
    $out .= "N4" .
      "*" . $claim->insuredCity($ins) .
      "*" . $claim->insuredState($ins) .
      "*" . $claim->insuredZip($ins) .
      "~\n";

    ++$edicount;
    $out .= "NM1" . // Loop 2330B Payer info for other insco. Page 359.
      "*PR" .
      "*2" .
      "*" . $claim->payerName($ins) .
      "*" .
      "*" .
      "*" .
      "*" .
      "*PI" .
      "*" . $claim->payerID($ins) .
      "~\n";

    // if (!$claim->payerID($ins)) {
    //   $log .= "*** CMS ID is missing for payer '" . $claim->payerName($ins) . "'.\n";
    // }

    // Payer address (N3 and N4) are added below so that Gateway EDI can
    // auto-generate secondary claims.  These do NOT appear in my copy of
    // the spec!  -- Rod 2008-06-12

    if (trim($claim->x12gsreceiverid()) == '431420764') { // if Gateway EDI
      ++$edicount;
      $out .= "N3" .
        "*" . $claim->payerStreet($ins) .
        "~\n";
      //
      ++$edicount;
      $out .= "N4" .
        "*" . $claim->payerCity($ins) .
        "*" . $claim->payerState($ins) .
        "*" . $claim->payerZip($ins) .
        "~\n";
    } // end Gateway EDI

  } // End loops 2320/2330*.

  $loopcount = 0;

  // Procedure loop starts here.
  //
  for ($prockey = 0; $prockey < $proccount; ++$prockey) {
    ++$loopcount;

    ++$edicount;
    $out .= "LX" .      // Loop 2400 LX Service Line. Page 398.
      "*$loopcount" .
      "~\n";

    ++$edicount;
    $out .= "SV1" .     // Professional Service. Page 400.
      "*HC:" . $claim->cptKey($prockey) .
      "*" . sprintf('%.2f', $claim->cptCharges($prockey)) .
      "*UN" .
      "*" . $claim->cptUnits($prockey) .
      "*" .
      "*" .
      "*";
    $dia = $claim->diagIndexArray($prockey);
    $i = 0;
    foreach ($dia as $dindex) {
      if ($i) $out .= ':';
      $out .= $dindex;
      if (++$i >= 4) break;
    }
    $out .= "~\n";

    if (!$claim->cptCharges($prockey)) {
      $log .= "*** Procedure '" . $claim->cptKey($prockey) . "' has no charges!\n";
    }

    if (empty($dia)) {
      $log .= "*** Procedure '" . $claim->cptKey($prockey) . "' is not justified!\n";
    }

    ++$edicount;
    $out .= "DTP" .     // Date of Service. Page 435.
      "*472" .
      "*D8" .
      "*" . $claim->serviceDate() .
      "~\n";

    // AMT*AAE segment for Approved Amount from previous payer.
    // Medicare secondaries seem to require this.
    //
    for ($ins = $claim->payerCount() - 1; $ins > 0; --$ins) {
      if ($claim->payerSequence($ins) > $claim->payerSequence())
        continue; // payer is future, not previous
      $payerpaid = $claim->payerTotals($ins, $claim->cptKey($prockey));
      ++$edicount;
      $out .= "AMT" . // Approved amount per previous payer. Page 485.
        "*AAE" .
        "*" . sprintf('%.2f', $claim->cptCharges($prockey) - $payerpaid[2]) .
        "~\n";
      break;
    }

    // Loop 2410, Drug Information. Medicaid insurers seem to want this
    // with HCPCS codes.
    //
    $ndc = $claim->cptNDCID($prockey);
    if ($ndc) {
      ++$edicount;
      $out .= "LIN" . // Drug Identification. Page 500+ (Addendum pg 71).
        "*" .         // Per addendum, LIN01 is not used.
        "*N4" .
        "*" . $ndc .
        "~\n";

      if (!preg_match('/^\d\d\d\d\d-\d\d\d\d-\d\d$/', $ndc, $tmp)) {
        $log .= "*** NDC code '$ndc' has invalid format!\n";
      }

      ++$edicount;
      $tmpunits = $claim->cptNDCQuantity($prockey) * $claim->cptUnits($prockey);
      if (!$tmpunits) $tmpunits = 1;
      $out .= "CTP" . // Drug Pricing. Page 500+ (Addendum pg 74).
        "*" .
        "*" .
        "*" . sprintf('%.2f', $claim->cptCharges($prockey) / $tmpunits) .
        "*" . $claim->cptNDCQuantity($prockey) .
        "*" . $claim->cptNDCUOM($prockey) .
        "~\n";
    }

    // Loop 2420A, Rendering Provider (service-specific).
    // Used if the rendering provider for this service line is different
    // from that in loop 2310B.
    //
    if ($claim->providerNPI() != $claim->providerNPI($prockey)) {
      ++$edicount;
      $out .= "NM1" .       // Loop 2310B Rendering Provider
        "*82" .
        "*1" .
        "*" . $claim->providerLastName($prockey) .
        "*" . $claim->providerFirstName($prockey) .
        "*" . $claim->providerMiddleName($prockey) .
        "*" .
        "*";
      if ($claim->providerNPI($prockey)) { $out .=
        "*XX" .
        "*" . $claim->providerNPI($prockey);
      } else { $out .=
        "*34" .
        "*" . $claim->providerSSN($prockey);
        $log .= "*** Rendering provider has no NPI.\n";
      }
      $out .= "~\n";

      if ($claim->providerTaxonomy($prockey)) {
        ++$edicount;
        $out .= "PRV" .
          "*PE" . // PErforming provider
          "*ZZ" .
          "*" . $claim->providerTaxonomy($prockey) .
          "~\n";
      }

      // REF*1C is required here for the Medicare provider number if NPI was
      // specified in NM109.  Not sure if other payers require anything here.
      if ($claim->providerNumber($prockey)) {
        ++$edicount;
        $out .= "REF" .
          "*" . $claim->providerNumberType($prockey) .
          "*" . $claim->providerNumber($prockey) .
          "~\n";
      }
    }

    // Loop 2430, adjudication by previous payers.
    //
    for ($ins = 1; $ins < $claim->payerCount(); ++$ins) {
      if ($claim->payerSequence($ins) > $claim->payerSequence())
        continue; // payer is future, not previous

      $payerpaid = $claim->payerTotals($ins, $claim->cptKey($prockey));
      $aarr = $claim->payerAdjustments($ins, $claim->cptKey($prockey));

      if ($payerpaid[1] == 0 && !count($aarr)) {
        $log .= "*** Procedure '" . $claim->cptKey($prockey) .
          "' has no payments or adjustments from previous payer!\n";
        continue;
      }

      ++$edicount;
      $out .= "SVD" . // Service line adjudication. Page 554.
        "*" . $claim->payerID($ins) .
        "*" . $payerpaid[1] .
        "*HC:" . $claim->cptKey($prockey) .
        "*" .
        "*" . $claim->cptUnits($prockey) .
        "~\n";

      $tmpdate = $payerpaid[0];
      foreach ($aarr as $a) {
        ++$edicount;
        $out .= "CAS" . // Previous payer's line level adjustments. Page 558.
          "*" . $a[1] .
          "*" . $a[2] .
          "*" . $a[3] .
          "~\n";
        if (!$tmpdate) $tmpdate = $a[0];
      }

      if ($tmpdate) {
        ++$edicount;
        $out .= "DTP" . // Previous payer's line adjustment date. Page 566.
          "*573" .
          "*D8" .
          "*$tmpdate" .
          "~\n";
      }
    } // end loop 2430
  } // end this procedure

  ++$edicount;
  $out .= "SE" .        // SE Trailer
    "*$edicount" .
    "*0021" .
    "~\n";

  $out .= "GE" .        // GE Trailer
    "*1" .
    "*1" .
    "~\n";

  $out .= "IEA" .       // IEA Trailer
    "*1" .
    "*000000001" .
    "~\n";

  $log .= "\n";
  return $out;
}
?>
