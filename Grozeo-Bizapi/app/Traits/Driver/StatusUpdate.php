<?php

namespace App\Traits\Driver;
use App\Http\Responses\ErrorResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
trait StatusUpdate
{
    function getQugeoParentStatusUpdated($updateUrl, $status)
    {
    
        $constants = config('constant.qugeo');
        $type = -1;
        // Check for specific patterns in the update URL
        if (strpos($updateUrl, '##31') !== false) {
            $type = 0;
        } elseif (strpos($updateUrl, '###1') !== false) {
            $type = 1;
        } elseif (strpos($updateUrl, '##21') !== false) {
            $type = 2;
        } elseif (strpos($updateUrl, '##61') !== false) {
            $type = 3;
        }
        if ($type == -1) {
            return new ErrorResponse("Could not find a valid status from drive status to update source");
        }

        switch ($status) {
            case 22:
                if ($type == 0) {
                    $strReturn = str_replace("##31", $constants['QUGEO_TO_CPD2BR_ORDER_STATUS_READY_FOR_DELIVERY'], $updateUrl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1", $constants['QUGEO_TO_B2C_ORDER_STATUS_READY_FOR_DELIVERY'], $updateUrl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", $constants['QUGEO_TO_B2B_ORDER_STATUS_READY_FOR_DELIVERY'], $updateUrl);
                } else {
                    $strReturn = str_replace("##61", 4, $updateUrl);
                }
                break;
            case 23:
            case 32:
                if ($type == 0) {
                    $strReturn = str_replace("##31", $constants['QUGEO_TO_CPD2BR_ORDER_STATUS_BOY_POLLED'], $updateUrl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1", $constants['QUGEO_TO_B2C_ORDER_STATUS_BOY_POLLED'], $updateUrl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", $constants['QUGEO_TO_B2B_ORDER_STATUS_BOY_POLLED'], $updateUrl);
                } else {
                    $strReturn = str_replace("##61", 4, $updateUrl);
                }
                break;
           case 24:
           case 33:
                if ($type == 0) {
                    $strReturn = str_replace("##31", $constants['QUGEO_TO_CPD2BR_ORDER_STATUS_POLL_REJECT_NORESPONSE'], $updateUrl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1", $constants['QUGEO_TO_B2C_ORDER_STATUS_POLL_REJECT_NORESPONSE'], $updateUrl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", $constants['QUGEO_TO_B2B_ORDER_STATUS_POLL_REJECT_NORESPONSE'], $updateUrl);
                } else {
                    $strReturn = str_replace("##61", 4, $updateUrl);
                }
                break;

                case 25:
                case 34:
                if ($type == 0) {
                    $strReturn = str_replace("##31", $constants['QUGEO_TO_CPD2BR_ORDER_STATUS_POLL_REJECT_NORESPONSE'], $updateUrl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1", $constants['QUGEO_TO_B2C_ORDER_STATUS_POLL_REJECT_NORESPONSE'], $updateUrl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", $constants['QUGEO_TO_B2B_ORDER_STATUS_POLL_REJECT_NORESPONSE'], $updateUrl);
                } else {
                    $strReturn = str_replace("##61", 4, $updateUrl);
                }
                break;

                case 26:
                case 27:
                if ($type == 0) {
                    $strReturn = str_replace("##31", $constants['QUGEO_TO_CPD2BR_ORDER_STATUS_BOY_ASSIGNED'], $updateUrl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1", $constants['QUGEO_TO_B2C_ORDER_STATUS_BOY_ASSIGNED'], $updateUrl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", $constants['QUGEO_TO_B2B_ORDER_STATUS_BOY_ASSIGNED'], $updateUrl);
                } else {
                    $strReturn = str_replace("##61", 4, $updateUrl);
                }
                break;

                case 28:
                    case 29:
                    if ($type == 0) {
                        $strReturn = str_replace("##31", $constants['QUGEO_TO_CPD2BR_ORDER_STATUS_PICKED_UP'], $updateUrl);
                    } elseif ($type == 1) {
                        $strReturn = str_replace("###1", $constants['QUGEO_TO_B2C_ORDER_STATUS_PICKED_UP'], $updateUrl);
                    } elseif ($type == 2) {
                        $strReturn = str_replace("##21", $constants['QUGEO_TO_B2B_ORDER_STATUS_PICKED_UP'], $updateUrl);
                    } else {
                        $strReturn = str_replace("##61", 4, $updateUrl);
                    }
                    break;

                case 9:
                    if ($type == 0) {
                        $strReturn = str_replace("##31", $constants['QUGEO_TO_CPD2BR_ORDER_STATUS_OUT_DELIVERY'], $updateUrl);
                    } elseif ($type == 1) {
                        $strReturn = str_replace("###1", $constants['QUGEO_TO_B2C_ORDER_STATUS_OUT_FOR_DELIVERY'], $updateUrl);
                    } elseif ($type == 2) {
                        $strReturn = str_replace("##21", $constants['QUGEO_TO_B2B_ORDER_STATUS_OUT_DELIVERY'], $updateUrl);
                    } else {
                        $strReturn = str_replace("##61", 4, $updateUrl);
                    }
                    break;

                case 31:
                    if ($type == 0) {
                        $strReturn = str_replace("##31", $constants['QUGEO_TO_CPD2BR_ORDER_STATUS_MANUAL_SCHEDULE'], $updateUrl);
                    } elseif ($type == 1) {
                        $strReturn = str_replace("###1", $constants['QUGEO_TO_B2C_ORDER_STATUS_MANUAL_SCHEDULE'], $updateUrl);
                    } elseif ($type == 2) {
                        $strReturn = str_replace("##21", $constants['QUGEO_TO_B2B_ORDER_STATUS_MANUAL_SCHEDULE'], $updateUrl);
                    } else {
                        $strReturn = str_replace("##61", 4, $updateUrl);
                    }
                    break;


                  case 35:
                                case 36:
                                case 37:
                        if ($type == 0) {
                            $strReturn = str_replace("##31", $constants['QUGEO_TO_CPD2BR_ORDER_STATUS_PICKUP_FAILED'], $updateUrl);
                        } elseif ($type == 1) {
                            $strReturn = str_replace("###1", $constants['QUGEO_TO_B2C_ORDER_STATUS_PICKUP_FAILED'], $updateUrl);
                        } elseif ($type == 2) {
                            $strReturn = str_replace("##21", $constants['QUGEO_TO_B2B_ORDER_STATUS_PICKUP_FAILED'], $updateUrl);
                        } else {
                            $strReturn = str_replace("##61", 4, $updateUrl);
                        }
                        break;


                        case 10:
                            case 11:
                            case 12:
                            case 13:
                            case 14:
                    if ($type == 0) {
                        $strReturn = str_replace("##31", $constants['QUGEO_TO_CPD2BR_ORDER_STATUS_DELIVERY_FAILED'], $updateUrl);
                    } elseif ($type == 1) {
                        $strReturn = str_replace("###1", $constants['QUGEO_TO_B2C_ORDER_STATUS_DELIVERY_FAILED'], $updateUrl);
                    } elseif ($type == 2) {
                        $strReturn = str_replace("##21", $constants['QUGEO_TO_B2B_ORDER_STATUS_DELIVERY_FAILED'], $updateUrl);
                    } else {
                        $strReturn = str_replace("##61", 4, $updateUrl);
                    }
                    break;


                    case 38:
                        if ($type == 0) {
                            $strReturn = str_replace("##31", $constants['QUGEO_TO_CPD2BR_ORDER_STATUS_DELIVERED_NOT_CONFIRMED'], $updateUrl);
                        } elseif ($type == 1) {
                            $strReturn = str_replace("###1", $constants['QUGEO_TO_B2C_ORDER_STATUS_DELIVERED_NOT_CONFIRMED'], $updateUrl);
                        } elseif ($type == 2) {
                            $strReturn = str_replace("##21", $constants['QUGEO_TO_B2B_ORDER_STATUS_DELIVERED_NOT_CONFIRMED'], $updateUrl);
                        } else {
                            $strReturn = str_replace("##61", 4, $updateUrl);
                        }
                        break;

                    case 15:
                        if ($type == 0) {
                            $strReturn = str_replace("##31", $constants['QUGEO_TO_CPD2BR_ORDER_STATUS_DELIVERY_CONFIRMED'], $updateUrl);
                        } elseif ($type == 1) {
                            $strReturn = str_replace("###1", $constants['QUGEO_TO_B2C_ORDER_STATUS_DELIVERY_CONFIRMED'], $updateUrl);
                        } elseif ($type == 2) {
                            $strReturn = str_replace("##21", $constants['QUGEO_TO_B2B_ORDER_STATUS_DELIVERY_CONFIRMED'], $updateUrl);
                        } else {
                            $strReturn = str_replace("##61", 4, $updateUrl);
                        }
                        break;

            
            default:
            
                return $updateUrl; 
        }
        return $strReturn;
    
    }
   
}
