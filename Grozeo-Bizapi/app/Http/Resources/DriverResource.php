<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'first_name'        => $this->d_Name,
            'last_name'         => $this->d_lName,
            'address1'          => $this->d_Add1,
            'address2'          => $this->d_Add2,
            'address3'          => $this->d_Add3,
            'phone'             => $this->d_Ph1,
            'email'             => $this->emp_email_id,
            'delivery_range'    => $this->d_DeliveryRange,
            'date_of_birth'     => $this->d_dob,
            'license'           => $this->d_licence,
            'license_expiry'    => $this->d_licenceexpairy,
            "order_limit"       => $this->order_limit,
            'branchDetails'     => new DriveBranchResource($this->branch),
            'primaryVehicle'    => new VehiclesResource($this->primaryVehicle)
        ];
    }
}
