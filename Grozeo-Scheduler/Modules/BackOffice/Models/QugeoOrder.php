<?php

namespace BackOffice\Models;

use Illuminate\Database\Eloquent\Model;
use BackOffice\Status\QugeoStatus;
use BackOffice\Status\QugeoSourceOrderStatus;
use Illuminate\Support\Facades\Log;


class QugeoOrder extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qugeo_order';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'quor_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function details()
    {
        return $this->hasOne(QugeoOrderDetails::class, 'quor_id');
    }
    public static function getQugeoParentStatusUpdated($updateurl, $status) {
        $pos = strpos($updateurl, '##31');
        if ($pos === false) {
            $pos = strpos($updateurl, '###1');
            if ($pos === false) {
                $pos = strpos($updateurl, '##21');
                if ($pos === false) {
                    $pos = strpos($updateurl, '##61');
                    if ($pos === false) {
                        throw new Exception("Could not find a valid status from drive status to update source");
                    } else {
                        $type = 3;
                    }
                } else {
                    $type = 2;
                }
            } else {
                $type = 1;
            }
        } else {
            $type = 0;
        }
        switch ($status) {
            case 22:
                if ($type == 0) {
                    $strReturn = str_replace("##31",QugeoSourceOrderStatus::QUGEO_TO_CPD2BR_ORDER_STATUS_READY_FOR_DELIVERY, $updateurl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1", QugeoSourceOrderStatus::QUGEO_TO_B2C_ORDER_STATUS_READY_FOR_DELIVERY, $updateurl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", QugeoSourceOrderStatus::QUGEO_TO_B2B_ORDER_STATUS_READY_FOR_DELIVERY, $updateurl);
                } else {
                    $strReturn = str_replace("##61", 4, $updateurl);
                }
                //Ready for delivery
                break;
            case 23:
            case 32:
                if ($type == 0) {
                    $strReturn = str_replace("##31", QugeoSourceOrderStatus::QUGEO_TO_CPD2BR_ORDER_STATUS_BOY_POLLED, $updateurl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1",QugeoSourceOrderStatus::QUGEO_TO_B2C_ORDER_STATUS_BOY_POLLED, $updateurl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", QugeoSourceOrderStatus::QUGEO_TO_B2B_ORDER_STATUS_BOY_POLLED, $updateurl);
                } else {
                    $strReturn = str_replace("##61", 4, $updateurl);
                }
                //Polled
                break;
            case 24:
            case 33:
                if ($type == 0) {
                    $strReturn = str_replace("##31", QugeoSourceOrderStatus::QUGEO_TO_CPD2BR_ORDER_STATUS_POLL_REJECT_NORESPONSE, $updateurl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1", QugeoSourceOrderStatus::QUGEO_TO_B2C_ORDER_STATUS_POLL_REJECT_NORESPONSE, $updateurl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", QugeoSourceOrderStatus::QUGEO_TO_B2B_ORDER_STATUS_POLL_REJECT_NORESPONSE, $updateurl);
                } else {
                    $strReturn = str_replace("##61", 4, $updateurl);
                }
                //Rejected
                break;
            case 25:
            case 34:
                if ($type == 0) {
                    $strReturn = str_replace("##31", QugeoSourceOrderStatus::QUGEO_TO_CPD2BR_ORDER_STATUS_POLL_REJECT_NORESPONSE, $updateurl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1", QugeoSourceOrderStatus::QUGEO_TO_B2C_ORDER_STATUS_POLL_REJECT_NORESPONSE, $updateurl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", QugeoSourceOrderStatus::QUGEO_TO_B2B_ORDER_STATUS_POLL_REJECT_NORESPONSE, $updateurl);
                } else {
                    $strReturn = str_replace("##61", 4, $updateurl);
                }
                //No response
                break;
            case 26:
            case 27:
                if ($type == 0) {
                    $strReturn = str_replace("##31", QugeoSourceOrderStatus::QUGEO_TO_CPD2BR_ORDER_STATUS_BOY_ASSIGNED, $updateurl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1", QugeoSourceOrderStatus::QUGEO_TO_B2C_ORDER_STATUS_BOY_ASSIGNED, $updateurl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", QugeoSourceOrderStatus::QUGEO_TO_B2B_ORDER_STATUS_BOY_ASSIGNED, $updateurl);
                } else {
                    $strReturn = str_replace("##61", 4, $updateurl);
                }
                // Assigned				
                break;
            case 28:
            case 29:
                if ($type == 0) {
                    $strReturn = str_replace("##31", QugeoSourceOrderStatus::QUGEO_TO_CPD2BR_ORDER_STATUS_PICKED_UP, $updateurl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1", QugeoSourceOrderStatus::QUGEO_TO_B2C_ORDER_STATUS_PICKED_UP, $updateurl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", QugeoSourceOrderStatus::QUGEO_TO_B2B_ORDER_STATUS_PICKED_UP, $updateurl);
                } else {
                    $strReturn = str_replace("##61", 4, $updateurl);
                }
                // PIcked UP ****
                break;
            case 9: //Out For Delivery
                if ($type == 0) {
                    $strReturn = str_replace("##31", QugeoSourceOrderStatus::QUGEO_TO_CPD2BR_ORDER_STATUS_OUT_DELIVERY, $updateurl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1", QugeoSourceOrderStatus::QUGEO_TO_B2C_ORDER_STATUS_OUT_FOR_DELIVERY, $updateurl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", QugeoSourceOrderStatus::QUGEO_TO_B2B_ORDER_STATUS_OUT_DELIVERY, $updateurl);
                } else {
                    $strReturn = str_replace("##61", 4, $updateurl);
                }
                break;
            case 31:
                if ($type == 0) {
                    $strReturn = str_replace("##31", QugeoSourceOrderStatus::QUGEO_TO_CPD2BR_ORDER_STATUS_MANUAL_SCHEDULE, $updateurl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1", QugeoSourceOrderStatus::QUGEO_TO_B2C_ORDER_STATUS_MANUAL_SCHEDULE, $updateurl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", QugeoSourceOrderStatus::QUGEO_TO_B2B_ORDER_STATUS_MANUAL_SCHEDULE, $updateurl);
                } else {
                    $strReturn = str_replace("##61", 4, $updateurl);
                }
                //Delivery schedule
                break;
            case 35:
            case 36:
            case 37:
                if ($type == 0) {
                    $strReturn = str_replace("##31", QugeoSourceOrderStatus::QUGEO_TO_CPD2BR_ORDER_STATUS_PICKUP_FAILED, $updateurl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1", QugeoSourceOrderStatus::QUGEO_TO_B2C_ORDER_STATUS_PICKUP_FAILED, $updateurl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", QugeoSourceOrderStatus::QUGEO_TO_B2B_ORDER_STATUS_PICKUP_FAILED, $updateurl);
                } else {
                    $strReturn = str_replace("##61", 4, $updateurl);
                }
                //PIckup failed
                break;
            case 10:
            case 11:
            case 12:
            case 13:
            case 14:
                if ($type == 0) {
                    $strReturn = str_replace("##31", QugeoSourceOrderStatus::QUGEO_TO_CPD2BR_ORDER_STATUS_DELIVERY_FAILED, $updateurl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1", QugeoSourceOrderStatus::QUGEO_TO_B2C_ORDER_STATUS_DELIVERY_FAILED, $updateurl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", QugeoSourceOrderStatus::QUGEO_TO_B2B_ORDER_STATUS_DELIVERY_FAILED, $updateurl);
                } else {
                    $strReturn = str_replace("##61", 4, $updateurl);
                }
                // delivery Failed
                break;
            case 38:
                if ($type == 0) {
                    $strReturn = str_replace("##31", QugeoSourceOrderStatus::QUGEO_TO_CPD2BR_ORDER_STATUS_DELIVERED_NOT_CONFIRMED, $updateurl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1",QugeoSourceOrderStatus::QUGEO_TO_B2C_ORDER_STATUS_DELIVERED_NOT_CONFIRMED, $updateurl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", QugeoSourceOrderStatus::QUGEO_TO_B2B_ORDER_STATUS_DELIVERED_NOT_CONFIRMED, $updateurl);
                } else {
                    $strReturn = str_replace("##61", 5, $updateurl);
                }
                //Delivered but not confirmed
                break;
            case 15:
                if ($type == 0) {
                    $strReturn = str_replace("##31", QugeoSourceOrderStatus::QUGEO_TO_CPD2BR_ORDER_STATUS_DELIVERY_CONFIRMED, $updateurl);
                } elseif ($type == 1) {
                    $strReturn = str_replace("###1", QugeoSourceOrderStatus::QUGEO_TO_B2C_ORDER_STATUS_DELIVERY_CONFIRMED, $updateurl);
                } elseif ($type == 2) {
                    $strReturn = str_replace("##21", QugeoSourceOrderStatus::QUGEO_TO_B2B_ORDER_STATUS_DELIVERY_CONFIRMED, $updateurl);
                } else {
                    $strReturn = str_replace("##61", 5, $updateurl);
                }
                //Deliverey confirmed
                break;
            default:
        }
   //     file_put_contents('php://stderr', "getQugeoParentStatusUpdated -- Whats " . $updateurl . " -- " . $status . " -- " . $type . "\n");
     //   file_put_contents('php://stderr', "getQugeoParentStatusUpdated -- " . $strReturn . "\n");
        return $strReturn;
    }

}
