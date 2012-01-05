<?

class KLdap {

    private $server;
    private $dn;

    public function __construct($server, $dn) {
        $this->server = $server;
        $this->dn = $dn;
    }

    public function login($user, $pass) {
        $userdn = "uid=$user," . $this->dn;
        if (ldap_bind(ldap_connect($this->server), $userdn, $pass)) {
            return true;
        } else {
            return false;
        }
    }

    public function groups($user) {
        $connection = ldap_connect($this->server);
        foreach(ldap_get_entries($connection,ldap_search($connection, $this->dn, "(&(cn=*)(memberuid=$user))", array('cn'))) as $entry){
            $groups[]=$entry['cn'][0];
        }
        return $groups;
    }

}

?>

