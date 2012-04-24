[default]
mvc:
    server=quench-api.dev
    applicationPath=app/
    baseRewrite=/
    route:
        suffix=
    default:
        module=
        controller=core
instances:
    mvc.router:
        class=\Config\Router
database.connector:
    host=localhost
    user=root
    password=
    name=quench
map.loader:
    file=config/orm.xml
map:
    defaults:
        attribute:
            searchable=0
instances:
    object:
        class=\Model\Core
    i18n.translation:
        class=\CRUDsader\I18n\Translation\Yaml
i18n.translation:
    file=config/translation.ini
api:
    limit=5
    maxLimit=100
    distance=4000
    google_map_api_key=gwehgiwehgiowehgioewho
    images:
        small=200x112.5
        medium=400x225	
        large@1x=306x205	
        large@2x=612x410
        thumb@1x=194x124 
        thumb@2x=82x62
        tiny_thumb@1x=63x48 
        tiny_thumb@2x=126x96	
        thumb1=90x50	
facebook:
    appId=309064122482892
    secret=3b5634a070eaa2e9b9b904103a0abca5
oauth:
    ttl=6000000000
email:
    live=0
    get_involved=getinvolved@quenchapp.com
url:
    media=http://www.quenchapp.dev/media/
    venues=http://venues.quenchapp.dev/
    site=http://www.quenchapp.dev/







#!DEFAULTS
[API:default]
debug:
    error=1
    databaseProfiler=0
mvc:
    baseRewrite=/
    modules:
        api=
[PUBLIC:default]
debug:
    error=1
    databaseProfiler=0
mvc:
    baseRewrite=/
    modules:
        public=
[ADMIN:default]
debug:
    error=1
    databaseProfiler=0
mvc:
    baseRewrite=/
    modules:
        admin=
    plugins:
        admin:
            authentication=
                login=admin
                password=$1$sQBIPsOT$4eLcdGZJn/QgJlW0c47NB0
[VENUES:default]
debug:
    error=1
    databaseProfiler=0
    redirection=0
mvc:
    baseRewrite=/
    modules:
        protected=
    plugins:
        protected:
            authentication=







#TEST IE
[192.168.1.37:API]
mvc:
    server=192.168.1.37
    baseRewrite=/quenchapi/







#DEV
[quenchapp.dev:PUBLIC]
mvc:
    server=www.quenchapp.dev
[www.quenchapp.dev:PUBLIC]
mvc:
    server=www.quenchapp.dev
[api.quenchapp.dev:API]
mvc:
    server=www.quenchapp.dev
[admin.quenchapp.dev:ADMIN]
mvc:
    server=admin.quenchapp.dev
[venues.quenchapp.dev:VENUES]
mvc:
    server=venues.quenchapp.dev








#DEV ONLINE
[alpha.www.quenchapp.com:PUBLIC]
mvc:
    server=alpha.www.quenchapp.com
database.connector:
    name=quenchap_alpha
    user=quenchap_alpha
    password=auJnuLZr65+UicdY
url:
    media=http://alpha.file.quenchapp.com/
    venues=http://alpha.venues.quenchapp.com/
    site=http://alpha.www.quenchapp.com/
[alpha.venues.quenchapp.com:VENUES]
mvc:
    server=alpha.venues.quenchapp.com
database.connector:
    name=quenchap_alpha
    user=quenchap_alpha
    password=auJnuLZr65+UicdY
url:
    media=http://alpha.file.quenchapp.com/
    venues=http://alpha.venues.quenchapp.com/
    site=http://alpha.www.quenchapp.com/
[alpha.api.quenchapp.com:API]
mvc:
    server=alpha.api.quenchapp.com
database.connector:
    name=quenchap_alpha
    user=quenchap_alpha
    password=auJnuLZr65+UicdY
url:
    media=http://alpha.file.quenchapp.com/
    venues=http://alpha.venues.quenchapp.com/
    site=http://alpha.www.quenchapp.com/
[alpha.admin.quenchapp.com:ADMIN]
mvc:
    server=alpha.admin.quenchapp.com
database.connector:
    name=quenchap_alpha
    user=quenchap_alpha
    password=auJnuLZr65+UicdY
url:
    media=http://alpha.file.quenchapp.com/
    venues=http://alpha.venues.quenchapp.com/
    site=http://alpha.www.quenchapp.com/







#STABLE
[beta.quenchapp.com:API]
mvc:
    server=api.quenchapp.dev
database.connector:
    name=quenchap_beta
    user=quenchap_beta
    password=+f,PYX]#oP4RwG@m
