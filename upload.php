<?php
header('Content-Type: application/json; charset=utf-8');

try {
    // validations
    if (empty($_FILES)) {
        throw new Exception('Files Param. is empty', 1);
    }

    // temp folder
    $tempFolder = __DIR__ . '/temp/';
    if (file_exists($tempFolder)) {
        if ( false == is_dir($tempFolder)) {
            throw new Exception('Error with temp Folder', 1);
        } 
    } else {
        if ( false == mkdir($tempFolder, 0777, true)) {
            throw new Exception('Error creating temp Folder', 1);
        }
    }

    // ** read uploaded file
    $fileName = $_FILES['file']['name'];
    $fileNameTemp  = $_FILES['file']['tmp_name'];    
    $uploadedData = file_get_contents($fileNameTemp);
    // ** uploaded Data validation
    if ( empty($uploadedData) ){
        throw new Exception('File is empty: ' . $fileName, 1);
    }
    $acf = json_decode($uploadedData, true);
    if ( false == is_array($acf) ){
        throw new Exception('File format is incorrect: ' . $fileName, 1);
    }
    if ( empty($acf[0]['fields']) ){
        throw new Exception('No fields to process: ' . $fileName, 1);
    }

    // ** convert acf data to jet and save file to server
    $jet = makeJet($acf[0]);
    $jetname =  'jet_' . $fileName;
    $jetFilename = $tempFolder .  $jetname;
    $jetData = json_encode($jet['jet']);
    $foo = file_put_contents($jetFilename, $jetData);
    $rv = array(
        'status'        => 'ok',
        'error'         => false,
        'msg'           => strlen($jetData),
        'jetFileName'   => $jetname
    );
    echo json_encode($rv);
} catch (Exception $e) {
    $re = array(
        'status' => 'error',
        'error' => true , 
        'msg'   => $e->getMessage()
    );
    echo json_encode($re);
}
exit;
// --------------------------------------------------------------------------------------

function makeJet($acf){
    // $id = date("YmdHis");
    $id = 'meta-' . date("His");
    $rv = array(
        'jet' =>array(
            'post_types' => [],
            'taxonomies' => [],
            'listings' => [],
            'relations' => [],
            'options_pages' => [],
            'glossaries' => [],
            'queries' => [],
            'content' => null,
            'meta_boxes' => [],
        )
    );

    $arg = array(
        'object_type' => 'post',
        'allowed_tax' => [],
        'allowed_post_type' => [ $acf['location'][0][0]['value'] ],
        'allowed_posts' => [],
        'excluded_posts' => [],
        'active_conditions' => [],
        'name' => $acf['title'],
        'show_edit_link' => true,
    );

    $types = array(
        'text'  => 'text',
        'textarea'  => 'textarea',
        'number'    => 'number',
        'range'    => 'number',
        'email'  => 'text',
        'url'  => 'text',
        'password'  => 'text',
        'image'  => 'media',
        'wysiwyg'  => 'wysiwyg',
        'oembed'  => 'text',
        'select'  => 'select',
        'checkbox'  => 'checkbox',
        'radio'  => 'radio',
        'button_group'  => 'radio',
        'true_false'  => 'switcher',
        'link'  => 'text',
        'post_object'  => 'text',
        'page_link'  => 'text',
        'date_picker'  => 'date',
        'time_picker'  => 'time',
        'date_time_picker'  => 'datetime-local',
        'color_picker'  => 'colorpicker',
        'relationship'  => false,
        'taxonomy'  => false,
        'user'  => false,
        'google_map'  => false,
    );

    $operators = array(
        '!=empty'   => '!empty',
        '==empty'   => 'empty',
        '==pattern'   => 'regexp',
        '!=pattern'   => '!regexp',
        '==contains'   => 'contains',
        '!=contains'   => '!contains',
        '=='         => 'equal',
        '!='         => 'not_equal',
        '>'         => 'greater_than',
        '<'         => 'less_than',

    );

    $pid = date("His");
    $fields = array();

    // array to relation the acf field with jet field
    // used for conditional logic
    $relations = array();

    // ** first loop - populate relations
    foreach ($acf['fields'] as $value) {
        $relations[$value['key']] = $value['name'];
    }

    //lw(' relations  ', $relations);

    // ** the loop
    $oid = date("is");
    foreach ($acf['fields'] as $key => $value) {
        if ( false == array_key_exists($value['type'],$types) ){
            continue;
        }
        if ( empty( $types[$value['type']] ) ){
            continue;
        }
        $mf = array(
            'title' => $value['label'],
            'name' => $value['name'],
            'is_required'     =>  $value['required'],
            'type' => $types[$value['type']],
            'width' => (empty($value['wrapper']['width']) ) ? '50%' : $value['wrapper']['width'],
            'options' => [],
            'repeater-fields' => [],
            'id' => $pid++ ,
            'object_type' => 'field',
            'isNested' => false,
            'options_source' => 'manual',
            'collapsed'     => false,
            'quick_editable'     => true,
            'revision_support'     => false,
            'show_in_rest'     => false,

        );

        if ( !empty($value['default_value']) ){
            $mf['default_val' ] = $value['default_value'];
        }
        if ( !empty($value['instructions']) ){
            $mf['description'] = $value['instructions'];
        }
        if (!empty($value['return_format'])) {
            switch ($value['return_format'] ){
                case 'array':
                    $mf['value_format'] = 'both';
                    break;
                default:
                    $mf['value_format'] = $value['return_format'];
                    break;
            }
            
        }

        // select options
        if ( in_array($value['type'] , ['select' , 'checkbox' , 'radio' , 'button_group' , 'rue_false'])  ){
            $options = array();
            
            foreach ($value['choices'] as $chk => $chv) {
                $option = array(
                    'key' => $chk,
                    'value' => $chv,
                    'id'    => $oid++
                );
                $options[] = $option;
            }
            $mf['options'] = $options;
            if ( array_key_exists('multiple' , $value) ){
                $mf['is_multiple'] = $value['multiple'];
            }
            
        }
        
        // * conditional logic
        if ( is_array($value['conditional_logic']) ){
            $mf['conditional_logic'] = true;
            $mf['conditional_relation'] = 'AND';
            $conditions = array();
            foreach ($value['conditional_logic'] as $cla) {
                foreach ($cla as $clv) {
                    $condition = array(
                        'field'         => $relations[$clv['field']] , 
                        'operator'      => $operators[$clv['operator']],
                        'value'         => (!empty($clv['value'])) ? $clv['value']:'' , 
                        'values'        => (!empty($clv['values'])) ? $clv['values']:'' , 
                        'collapsed'     => false,
                        'id'            =>  $oid++
                    );
                    $conditions[] = $condition;
                }
            }
            $mf['conditions'] = $conditions;
        }




        // insert element
        $fields[] = $mf;
    }

    $meta = array(
        'id' => $id,
        'args' => $arg,
        'meta_fields' => $fields,

    );

    $rv['jet']['meta_boxes'][$id] = $meta;

    return $rv;
}

function lw($msg = '', $debugInfo = false)
{
	$msgError = date("Y-m-d H:i:s") . ' - ' . $msg . PHP_EOL;
	if (!empty($debugInfo)) {
		$msgError .= '*** Debug Info ' . var_export($debugInfo, true) . PHP_EOL;
	}

	$folder = __DIR__;
	$filename = $folder . '/_upload.log';
	file_put_contents($filename, $msgError, FILE_APPEND);
}

