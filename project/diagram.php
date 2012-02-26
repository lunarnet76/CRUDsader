
<style type="text/css">
    ul{
        margin:0px;
        padding: 0px;
        list-style: none;
    }
    .entity{
        border:1px solid grey;
        float:left;
        width:150px;
    }
    .entity.developed{
    }
    .entity .class,.entity .table{
        float: left;
        width: 100%;
    }
    .entity .title{
        width:100%;
        height: 20px;
        background-color: grey;
        color:white;
    }
    .entity .class ul.attributes{
        margin:0px;
        padding:0px;
    }
    .entity .class ul.attributes li{
        padding-left: 5px;
    }
    .entity .class .title .className{
        width:125px;
        float:left;
        padding-left:5px;
    }
    .entity .class .title ul.menu{
        list-style: none;
        width:20px;
        float:left;

    }
    
    ul.attributes .required{
        text-decoration: underline ;
    }



    .entity .title .close{
        float:right;
    }
</style>

<script src="lib/jquery-1.7.1.min.js" type="text/javascript"></script>
<script src="lib/jquery-ui-1.8.18.custom.min.js" type="text/javascript"></script>
<script>
    
    
    var UmlEntity = function($options){
        var $_defaults = {
            
        };
        var $I = $.extend({}, $_defaults, $options);
        var $self = this;
        
        // constructor
        $self.show = function(){
            var $identity = $I.identity.split(',');
            
            var $containerAttributes = $('<ul/>').addClass('attributes');
            var $containerColumns = $('<ul/>').addClass('attributes');
            
            for($i in $I.attributes){
                $attribute = $('<li/>')
                .text($i);
                if($I.attributes[$i].required)
                    $attribute.addClass('required');
                if($.inArray($I.attributes[$i].name,$identity))
                    $attribute.append('<div class="icon identity"></div>');
                console.log($attribute);
                $containerAttributes.append($attribute);
            }
            
            console.log($containerAttributes);
            
            
            var $container = $('<div/>')
            .addClass('entity')
            .addClass('developed')
            .addClass('draggable')
            .append(
            $('<div/>')
            .addClass('class')
            .append(
            $('<div/>')
            .addClass('title')
            .append(
            $('<div/>')
            .addClass('className')
            .html($I.name)
        )
            .append(
            $('<ul/>')
            .addClass('menu')
            .append(
            $('<li/>')
            .addClass('close')
            .text('x')
        )
        )
        )
            .append(
            $containerAttributes
        )
        )
            /*.append(
                $('<div/>')
                .addClass('table')
                .append(
                    $('<div/>')
                    .addClass('title')
                    .append(
                        $('<div/>')
                        .addClass('className')
                        .html($I.databaseTable)
                    )
                    .append(
                        $('<div/>')
                        .addClass('phpClass')
                        .html($I.phpClass)
                    )
                )
                .append(
                    $containerColumns
                )
            )*/;
                
            $container.draggable();
                
            $('body').append($container);
        }
    };

    $(function() {
        $( ".draggable" ).draggable();
            
        new UmlEntity({
                name:'venue',
                databaseIdField:"id",
                databaseTable:"Venues",
                identity:"name,location",
                inherit:"base",
                phpClass:"app\\Model\\Venue",
                attributes:{
                    'name':{
                        required:true,
                        calculated:false,
                        databaseField:"hello",
                        'default':null,
                        html:null,
                        input:null,
                        json:null,
                        searchable:null,
                        type:null
                    }
                }
                    
            }).show();
            
    });


    

</script>