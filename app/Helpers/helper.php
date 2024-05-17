<?php 

if(!function_exists('getAuthUserRole')){
    function getAuthUserRole(){
        $rolewithadmin=auth()->user()->role;
        //$rolepermissions=($rolewithadmin)?$rolewithadmin->role->permissions:[];
        //dd($rolepermissions->toArray());
        return isset($rolewithadmin)?$rolewithadmin->toArray():[];
    }
}

if(!function_exists('getAuthUserPermissions')){
    function getAuthUserPermissions(){
        if(auth()->user()->is_administator && false){
            $rolewithadmin=\DB::table('permissions')->pluck('slug');
            return isset($rolewithadmin)?$rolewithadmin->toArray():[];
        }else{
            $rolewithadmin=auth()->user()->role;
            $rolepermissions=($rolewithadmin)?$rolewithadmin->role->permissions:[];
            return (isset($rolewithadmin) && isset($rolepermissions))?$rolepermissions->pluck('slug')->toArray():[];
        }
    }
}

if(!function_exists('getHashid')){
    function getHashid()
    {
        return md5(uniqid(rand(), true));
        //return sha1(time());
    }
}

if(!function_exists('object_to_array')){
    function object_to_array($object)
    {
        $array = (array)$object;
        array_walk_recursive($array, function(&$item){
            if(is_object($item)) $item = (array)$item;
        });
        return  $array;
    }
}


if(!function_exists('cleanObject')){
    function cleanObject($request)
    {
        if($request){
            
            $modelAsArray = gettype($request) == 'object' ? $request->toArray() : $request;
            //$modelAsArray = (array)json_decode(json_encode($modelAsArray), true);
            array_walk_recursive($modelAsArray, function (&$item, $key) {
                $item = ($item === NULL) ? '' : $item;
            });
            return $modelAsArray;
            //return array_filter($modelAsArray);
        }else{
            return array();
        }
        
    }
}

if(!function_exists('company_meta')){
    function companymeta()
    {
        $metadata=\DB::table('company_meta')->pluck('meta_value','meta_key');
        return $metadata;
    }
}

if(!function_exists('setSMTPConfig')){
    function setSMTPConfig()
    {
        $metaData=companymeta();

        $config = array(
            'transport'  => $metaData['mail_transport'],
            'host'       => $metaData['mail_host'],
            'port'       => $metaData['mail_port'],
            'encryption' => $metaData['mail_encryption'],
            'username'   => $metaData['mail_from_address'],
            'password'   => $metaData['mail_password'],
            'fromname'   => $metaData['mail_from_name'],
        );
        Config::set('mail.mailers.smtp', $config);
    }
}


if(!function_exists('activityEnums')){
    function activityEnums($keyofreq)
    {
        $arr=[
            'staff'               => 'staff',
            'customer'            => 'customer',
            'lead'                => 'lead',
            'company_settings'    => 'company_settings',
            'role'                => 'role',
            'email_template'      => 'email_template',
            'source'              => 'source',
            'customers-groups'    => 'customers-groups',
            'currencies'          => 'currencies',
            'expences-categories' => 'expences-categories',
            'contract-types'      => 'contract-types',
            'payment-modes'       => 'payment-modes',
            'taxes'               => 'taxes',
            'status'              => 'status',
            'announcement'        => 'announcement',
            'itemgroup'           => 'itemgroup',
            'products'            => 'products',
            'custom_fields'       => 'custom_fields',
            'custom-field-value'  => 'custom-field-value',
            'web_to_lead'         => 'web_to_lead',
            'tasks'               => 'tasks',
            'task-checklist'      => 'task-checklist',
            'task-comments'       => 'task-comments',
            'task-documents'      => 'task-documents',
            'notes'               => 'notes',
            'reminders'           => 'reminders',
            'lead-documents'      => 'lead-documents',
        ];
        return $arr[$keyofreq];
    }
}

if(!function_exists('setActivityLog')){
    function setActivityLog($desc='',$additional_data='',$rel_type,$rel_id=0,$auth_id=0)
    {
        $data = array(
            'hash_id'         => getHashid(),
            'description'     => $desc,
            'additional_data' => $additional_data,
            'ipaddress'       => \Request::ip(),
            'rel_type'        => $rel_type,
            'rel_id'          => $rel_id,
            'added_by'        => $auth_id,
        );
        DB::table('activity_logs')->insert($data);
    }
}

if(!function_exists('dissmissed_announcement')){
    function dissmissed_announcement($announcementid,$userid,$reftype='staff')
    {
        $data=DB::table('dismissed_announcement')->where('announcement_id',$announcementid)->where('reftype',$reftype)->where('ref_id',$userid)->first();
        if(!$data){
            $data = array(
                'announcement_id' => $announcementid,
                'reftype'         => $reftype,
                'ref_id'          => $userid,
            );
            DB::table('dismissed_announcement')->insert($data);
        }
        return true;
        
    }
}

if(!function_exists('systemInBuildField')){
    function systemInBuildField()
    {
        $arr = [];

        $arr['lead']['system_field'] = [
            'first_name'=> [
                "slug"       => 'first_name',
                "name"       => 'First Name',
                "field_to"   => 'lead',
                "validation" => 'required|max:255',
                "type"       => 'input',
                "bs_column"  => '6',
            ],
            'last_name'=> [
                "slug"       => 'last_name',
                "name"       => 'Last Name',
                "field_to"   => 'lead',
                "validation" => 'required|max:255',
                "type"       => 'input',
                "bs_column"  => '6',
            ],
            'company'=> [
                "slug"       => 'company',
                "name"       => 'Company',
                "field_to"   => 'lead',
                "validation" => 'max:255',
                "type"       => 'input',
                "bs_column"  => '6',
            ],
            'job_title'=> [
                "slug"       => 'job_title',
                "name"       => 'Job Title',
                "field_to"   => 'lead',
                "validation" => 'max:255',
                "type"       => 'input',
                "bs_column"  => '6',
            ],
            'city'=> [
                "slug"       => 'city',
                "name"       => 'City',
                "field_to"   => 'lead',
                "validation" => 'nullable|max:100',
                "type"       => 'input',
                "bs_column"  => '6',
            ],
            'state'=> [
                "slug"       => 'state',
                "name"       => 'State',
                "field_to"   => 'lead',
                "validation" => 'nullable|max:100',
                "type"       => 'input',
                "bs_column"  => '6',
            ],
            'country_id'=> [
                "slug"       => 'country_id',
                "name"       => 'Country',
                "field_to"   => 'lead',
                "validation" => 'required',
                "type"       => 'select',
                "bs_column"  => '6',
            ],
            'zip'=> [
                "slug"       => 'zip',
                "name"       => 'Zip',
                "field_to"   => 'lead',
                "validation" => 'nullable|integer|regex:/^\d{2,8}$/',
                "type"       => 'input',
                "bs_column"  => '6',
            ],
            'address'=> [
                "slug"       => 'address',
                "name"       => 'Address',
                "field_to"   => 'lead',
                "validation" => 'nullable|max:1500',
                "type"       => 'textarea',
                "bs_column"  => '6',
            ],
            'address1'=> [
                "slug"       => 'address1',
                "name"       => 'Address1',
                "field_to"   => 'lead',
                "validation" => 'nullable|max:1500',
                "type"       => 'textarea',
                "bs_column"  => '6',
            ],
            'address2'=> [
                "slug"       => 'address2',
                "name"       => 'Address2',
                "field_to"   => 'lead',
                "validation" => 'nullable|max:1500',
                "type"       => 'textarea',
                "bs_column"  => '6',
            ],
            'email'=> [
                "slug"       => 'email',
                "name"       => 'Email',
                "field_to"   => 'lead',
                "validation" => 'required|email|max:200|unique:leads,email',
                "type"       => 'input',
                "bs_column"  => '6',
            ],
            'country_code' => [
                "slug"       => 'country_code',
                "name"       => 'Country Code',
                "field_to"   => 'lead',
                "validation" => 'nullable|integer|regex:/^\d{1,3}$/',
                "type"       => 'input',
                "bs_column"  => '6',
            ],
            'phone' => [
                "slug"       => 'phone',
                "name"       => 'Phone',
                "field_to"   => 'lead',
                "validation" => 'nullable|integer|regex:/^\d{7,12}$/',
                "type"       => 'input',
                "bs_column"  => '6',
            ],
            'description' => [
                "slug"       => 'description',
                "name"       => 'Description',
                "field_to"   => 'lead',
                "validation" => 'nullable|max:1500',
                "type"       => 'textarea',
                "bs_column"  => '6',
            ],
           
        ];

        $arr['customer']['system_field'] = [
            'first_name'=> [
                "slug"       => 'first_name',
                "name"       => 'First Name',
                "field_to"   => 'customer',
                "validation" => 'required|max:255',
                "type"       => 'input',
                "bs_column"  => '6',
            ],
            'last_name'=> [
                "slug"       => 'last_name',
                "name"       => 'Last Name',
                "field_to"   => 'customer',
                "validation" => 'required|max:255',
                "type"       => 'input',
                "bs_column"  => '6',
            ],
            'email'=> [
                "slug"       => 'email',
                "name"       => 'Email',
                "field_to"   => 'customer',
                "validation" => 'required|email|max:200|unique:customer,email',
                "type"       => 'input',
                "bs_column"  => '6',
            ],
           
        ];

        return $arr;
    }
}

if(!function_exists('arrayByToKeys')){
    function arrayByToKeys($array,$array_key,$array_val='')
    {
        $output = [];
        if ($array) {
            foreach ($array as $key=>$val) {
                $val = (array)$val;
                if(!empty($array_val)){
                    $output[$val[$array_key]] = $val[$array_val];
                }else{
                    $output[$val[$array_key]] = $val;
                }
            }
        }
        return $output;
    }
}

if(!function_exists('arrayByCustomFieldValue')){
    function arrayByCustomFieldValue($array,$leadIds,$type)
    {
        $customFields = [];
        if ($array) {
            $CustomValueRes = DB::table('custom_field_value')->join('custom_fields', function($join) {
                    $join->on('custom_field_value.field_id', '=', 'custom_fields.id')->whereNull('custom_field_value.deleted_at');
                })
                ->select('custom_fields.hash_id as field_hash_id','custom_field_value.field_id','custom_field_value.value','custom_fields.name','custom_field_value.rel_column_id')
                ->where('custom_field_value.field_to','=','lead')
                ->whereIn('custom_field_value.rel_column_id',$leadIds)
                ->get();

            foreach ($CustomValueRes as $key=>$val) {
                $leadId     = $val->rel_column_id;
                $name       = $val->name;
                $f_id       = $val->field_id;
                $field_hash_id       = $val->field_hash_id;
                $fieldKey   = $name.'|||'.$f_id.'|||'.$field_hash_id;
                $fieldValue = $val->value;

                if (!isset($customFields[$leadId])) {
                    $customFields[$leadId] = [];
                }
            
                if (!isset($customFields[$leadId][$fieldKey])) {
                    $customFields[$leadId][$fieldKey] = [];
                }
                $customFields[$leadId][$fieldKey] = $fieldValue;
            }
        }
        return $customFields;
    }
}

if(!function_exists('render_template')){
    function render_template($json_array,$system_field,$custom_field,$hash_id,$button_name='SUBMIT'){
        $bodyform='<form data-redirect="{success_redirect_url}" action="'.route("web_to_lead_submit").'" method="POST" class="form-horizontal" id="myForm">
            <input type="hidden" name="hash_id" id="hash_id" value="'.$hash_id.'">
            <input name="_token" type="hidden" value="'.csrf_token().'"/>
            <div class="container">';
                if($json_array){
                    foreach($json_array as $key =>$val) {
                        $array = $val ? explode("-",$val): '';
                        $system_custom_name = $array?$array[0]:'';
                        $key_name = $array?$array[1]:'';
                        $field = isset($system_field[$key_name]) ? $system_field[$key_name] : (isset($custom_field[$key_name])?$custom_field[$key_name]:'');
                        if($field && $key_name){
                            if(in_array($key_name, $field)) {
                                if($system_custom_name == 'custom_field'){
                                    $field['slug'] = $key_name;
                                }
                                $bodyform .= '<div class="form-group col-sm-'.$field['bs_column'].'">
                                    <label class="control-label col-sm-4" >'.$field['name'].':</label>
                                    <div class="col-sm-6">';
                                        if ($field['type'] == 'input' || $field['type'] == 'number' || $field['type'] == 'color' || $field['type'] == 'hidden') {
                                            $bodyform .= '<input type="'.$field['type'].'" class="form-control" id="'.$field['slug'].'" placeholder="'.$field['name'].'" name="form_data['.$system_custom_name.']['.$field['slug'].']" '.((str_contains($field['validation'], 'required'))?'required=true':'').'">';
                                        } elseif ($field['type'] == 'textarea') {
                                            $bodyform .= '<textarea rows="5" class="form-control" id="'.$field['slug'].'" placeholder="'.$field['name'].'" name="form_data['.$system_custom_name.']['.$field['slug'].']" '.(str_contains($field['validation'], 'required')?'required':'').'"></textarea>';
                                        } elseif ($field['type'] == 'datepicker') {
                                            $bodyform .= '<input type="date" class="form-control datepicker" id="'.$field['slug'].'" name="form_data['.$system_custom_name.']['.$field['slug'].']" '.(str_contains($field['validation'], 'required')?'required=true':'').'">';
                                        } elseif ($field['type'] == 'date_picker_time') {
                                            $bodyform .= '<input type="datetime-local" class="form-control datetimepicker" id="'.$field['slug'].'" name="form_data['.$system_custom_name.']['.$field['slug'].']" '.(str_contains($field['validation'], 'required')?'required=true':'').'">';
                                        } elseif ($field['type'] == 'select') {
                                            $bodyform .= '<select class="form-control" id="'.$field['slug'].'" name="form_data['.$system_custom_name.']['.$field['slug'].']" '.(str_contains($field['validation'], 'required')?'required=true':'').'">';
                                            $select_value = $field['options']?explode(',', $field['options']):'';
                                            $bodyform .= '<option value="">Please Select</option>';
                                            if ($select_value) {
                                                foreach($select_value as $option) {
                                                    $bodyform .= '<option value="'.$option.'">'.$option.'</option>';
                                                }
                                            }
                                            $bodyform .= '</select>';
                                        } elseif ($field['type'] == 'radio') {
                                            $radio_value = $field['options']?explode(',', $field['options']):'';
                                            if ($radio_value) {
                                                foreach ($radio_value as $option) {
                                                    $bodyform .= '<label class="radio-inline">
                                                    <input type="radio" name="form_data['.$system_custom_name.']['.$field['slug'].']" value="'.$option.'" '.(str_contains($field['validation'], 'required')?'required=true':'').'"> '.$option.'
                                                    </label>';
                                                }
                                            }
                                        } elseif ($field['type'] == 'checkbox') {
                                            $checkbox = $field['options']?explode(',', $field['options']):'';
                                            if ($checkbox) {
                                                $bodyform .= '<div class="checkbox">';
                                                foreach($checkbox as $option) {
                                                    $bodyform .= '<label>
                                                            <input type="checkbox" name="form_data['.$system_custom_name.']['.$field['slug'].'][]" value="'.$option.'" '.(str_contains($field['validation'], 'required')?'required=true':'').'">
                                                            '.$option.'
                                                        </label>&nbsp';
                                                }
                                                $bodyform .= '</div>';
                                            }
                                        } elseif ($field['type'] == 'multiselect') {
                                            $multiselect_value = $field['options']?explode(',', $field['options']):'';
                                            $bodyform .= '<select class="form-select" name="form_data['.$system_custom_name.']['.$field['slug'].'][]" multiple '.(str_contains($field['validation'], 'required')?'required=true':'').'">';
                                            if ($multiselect_value) {
                                                foreach($multiselect_value as $optionLabel) {
                                                    $bodyform .= '<option value="'.$optionLabel.'">'.$optionLabel.'</option>';
                                                }
                                            }
                                            $bodyform .= '</select>';
                                        }
                                    $bodyform .= '</div>
                                </div>';
                            }
                        }
                    }
                }
                $bodyform .= '<div class="form-group">        
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" id="submit" class="btn btn-default">'.$button_name.'</button>
                    </div>
                </div>
            </div>
        </form>';

        return $bodyform;

    }
}




?>