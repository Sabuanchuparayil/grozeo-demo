<?php

namespace Models; {

    class Utils {

        public function getLedgerBalance($ledgerid, $isWallet = true) {
            $cgodb = new \cgoSqlDB();
            if ($isWallet)
            {
                $ledg = $cgodb->getFromDB("Select (accled_Credits + coalesce(if(coalesce(OpenBal_IsDebtor,0)=0,coalesce(OpenBal_Amt,0),0))) as Credits, (accled_Debits + coalesce(if(coalesce(OpenBal_IsDebtor,0)=1,coalesce(OpenBal_Amt,0),0))) as Debits,accled_Ledger_Id from finascop_accounts_ledger left join finascop_accounts_openingbalance on openBal_Led_ID = accled_Ledger_Id where accled_RefIdCRC32 = ?  and accled_ReferenceId = ? ", array('is', crc32($ledgerid), $ledgerid), true);
            }
            return $ledg;
        }

    }

}																										
