<?php
if (!defined("MODULE_DB_SHOP_SHIPPING_TO_COUNTRY")) {
    define('MODULE_DB_SHOP_SHIPPING_TO_COUNTRY', MW_TABLE_PREFIX . 'cart_shipping');
}
api_hook('shop/shipping/gateways/country/shipping_to_country/test', 'shop/shipping/gateways/country/shipping_to_country/test2');

// print('shop/shipping/gateways/country/shipping_to_country/test'. 'shop/shipping/gateways/country/shipping_to_country/test2');
api_expose('shop/shipping/gateways/country/shipping_to_country/save');
api_expose('shop/shipping/gateways/country/shipping_to_country/set');
api_expose('shop/shipping/gateways/country/shipping_to_country/get');
api_expose('shop/shipping/gateways/country/shipping_to_country/delete');
api_expose('shop/shipping/gateways/country/shipping_to_country/reorder');

class shipping_to_country
{

    // singleton instance
    public $table;

    // private constructor function
    // to prevent external instantiation
    function __construct()
    {
        $this->table = MW_TABLE_PREFIX . 'cart_shipping';
        $this->db_init();
    }


    function test()
    {
        return 'ping ';
    }

     function db_init() {
        $function_cache_id = false;

        $args = func_get_args();

        foreach ($args as $k => $v) {

            $function_cache_id = $function_cache_id . serialize($k) . serialize($v);
        }

        $function_cache_id = 'shipping_'.__FUNCTION__ . crc32($function_cache_id);

        $cache_content = cache_get_content($function_cache_id, 'db');

        if (($cache_content) != false) {

            return $cache_content;
        }

        $table_name = MODULE_DB_SHOP_SHIPPING_TO_COUNTRY;

        $fields_to_add = array();
        $fields_to_add[] = array('updated_on', 'datetime default NULL');
        $fields_to_add[] = array('created_on', 'datetime default NULL');
        $fields_to_add[] = array('is_active', "char(1) default 'y'");

        $fields_to_add[] = array('shiping_cost', 'float default NULL');
        $fields_to_add[] = array('shiping_cost_max', 'float default NULL');
        $fields_to_add[] = array('shiping_cost_above', 'float default NULL');

        $fields_to_add[] = array('shiping_country', 'TEXT default NULL');
        $fields_to_add[] = array('position', 'int(11) default NULL');


        \mw\DbUtils::build_table($table_name, $fields_to_add);

        //\mw\DbUtils::add_table_index('shiping_country', $table_name, array('shiping_country'));

        cache_save(true, $function_cache_id, $cache_group = 'db');
        return true;

        //print '<li'.$cls.'><a href="'.admin_url().'view:settings">newsl etenewsl etenewsl etenewsl etenewsl etenewsl etenewsl etenewsl etenewsl etenewsl etenewsl etenewsl etenewsl etenewsl etenewsl etenewsl eter</a></li>';
    }

    function test2()
    {
        return 'pong ';
    }

    // getInstance method
    function save($data)
    {
        if (is_admin() == false) {
            error('Must be admin');

        }

        if (isset($data['shiping_country'])) {
            if ($data['shiping_country'] == 'none') {
                error('Please choose country');
            }
            if (isset($data['id']) and intval($data['id']) > 0) {

            } else {
                $check = $this->get('shiping_country=' . $data['shiping_country']);
                if ($check != false and isarr($check[0]) and isset($check[0]['id'])) {
                    $data['id'] = $check[0]['id'];
                }
            }
        }


        $data = \mw\Db::save($this->table, $data);
        return ($data);
    }

    function get($params = false)
    {

        $params2 = array();
        if ($params == false) {
            $params = array();
        }
        if (is_string($params)) {
            $params = parse_str($params, $params2);
            $params = $params2;
        }

        $params['table'] = $this->table;

        if (!isset($params['order_by'])) {
            $params['order_by'] = 'position ASC';
        }
        $params['limit'] = 1000;
        // d($params);
        return get($params);

    }

    function delete($data)
    {

        $adm = is_admin();
        if ($adm == false) {
            error('Error: not logged in as admin.' . __FILE__ . __LINE__);
        }

        if (isset($data['id'])) {
            $c_id = intval($data['id']);
            \mw\Db::delete_by_id($this->table, $c_id);

            //d($c_id);
        }
        return true;
    }

    function set($params = false)
    {

        if (isset($params['country'])) {
            $active = $this->get('fields=shiping_country,shiping_cost_max,shiping_cost,shiping_cost_above&one=1&is_active=y&shiping_country=' . $params['country']);
            if (isarr($active)) {
                foreach ($active as $name => $val) {
                    session_set($name, $val);
                }
            } else {
                $active_ww = $this->get('fields=shiping_country,shiping_cost_max,shiping_cost,shiping_cost_above&one=1&is_active=y&shiping_country=Worldwide');
                if (isarr($active_ww)) {

                    $active_ww['shiping_country'] = $params['country'];


                    foreach ($active_ww as $name => $val) {
                        session_set($name, $val);
                    }

                    return $active_ww;

                }

            }
            return $active;
        }


    }

    function reorder($data)
    {

        $adm = is_admin();
        if ($adm == false) {
            error('Error: not logged in as admin.' . __FILE__ . __LINE__);
        }

        $table = $this->table;


        foreach ($data as $value) {
            if (is_arr($value)) {
                $indx = array();
                $i = 0;
                foreach ($value as $value2) {
                    $indx[$i] = $value2;
                    $i++;
                }

                \mw\DbUtils::update_position_field($table, $indx);
                return true;
                // d($indx);
            }
        }
    }


}
