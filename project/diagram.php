
<style type="text/css">
    body{
        margin:0;
        padding:0;
    }

    ul{
        margin:0px;
        padding: 0px;
        list-style: none;
    }
    .entity{
        border:1px solid grey;
       
        z-index: 1;
        background:white;
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
        padding-left: 25px;
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
        background:url(extra/images/pk16x16.png) 3px 3px no-repeat;
        
    }
    ul.attributes .calculated{
        background:url(extra/images/math16x16.png) 2px 1px no-repeat;
    }



    .entity .title .close{
        float:right;
    }


    .entity.user{
        top:50px;
        left:250px;
        position:absolute;
    }

    .association{
        z-index: 0;
    }

    .association path{
        stroke: #666;
        stroke-width: 5;
    }
    .inheritance{
        stroke: #ae1414;
        stroke-width: 5;
    }
</style>

<script src="lib/jquery-1.7.1.min.js" type="text/javascript"></script>
<script src="lib/jquery-ui-1.8.18.custom.min.js" type="text/javascript"></script>
<script>
    var $xml;
    
    var UmlEntity = function($options){
    
        var $DEFAULTS = {
            
        };
        var $self = this;
        
        var $I = $.extend({}, $DEFAULTS, $options);// Info
        var $E = {}; // DOM elements
        var $O = []; // Observers
        
        $self.getInfo = function($index){
            return $I[$index];
        }
        
        $self.getElement = function($name){
            return $E[$name];
        }
        
        $self.addObserver = function($observer){
            $O.push($observer);
        }
        
        $self.hasObserver = function($observer){
            for($i in $O)
                if($O[$i] == $observer)
                    return true;
            return false;
        }
        
        $self.remove = function(){
            $E.container.remove();
            for($i in $O)
                $O[$i].remove();
        }
        
        // constructor
        $self.draw = function(){
            
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
              
        }
        
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
            if($I.attributes[$i].calculated)
                $attribute.addClass('calculated');
            
            $containerAttributes.append($attribute);
        }
            
        $E.container = $('<div/>')
        .addClass('entity')
        .addClass('developed')
        .addClass('draggable')
        .css({position:'absolute'})
        .addClass($I.name)
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
        .bind('click',$self.remove)
    )
    )
    )
        .append(
        $containerAttributes
    )
    )
        
        $E.container.draggable({
            stop:function(){
                for($i in $O)
                    $O[$i].draw()
                // update xml x y 
                $pos1 = $xml.indexOf('<class name="'+$I.name+'"');
                $pos2 = $xml.substring($pos1).indexOf('>');
                $classXmlTag = $xml.substring($pos1,$pos1+$pos2);
                $regexp = /x="([0-9]*)"\sy="([0-9]*)"/
                $x = $E.container.offset().left;
                $y = $E.container.offset().top;
                if($classXmlTag.match($regexp)){
                    console.log($classXmlTag);
                    $xml = $xml.replace($classXmlTag,$classXmlTag.replace($regexp,'x="'+$x+'" y="'+$y+'"'));
                }else{
                    $xml = $xml.replace($classXmlTag,$classXmlTag+' x="'+$x+'" y="'+$y+'"');
                }
            }
        });
              
        $('body').append($E.container);
        
        $pos1 = $xml.indexOf('<class name="'+$I.name+'"');
        $pos2 = $xml.substring($pos1).indexOf('>');
        $classXmlTag = $xml.substring($pos1,$pos1+$pos2+1);
        $regexp = /x="([0-9]*)"\sy="([0-9]*)"/
        
        if($classXmlTag.match($regexp)){
            $E.container.css({left:RegExp.$1,top:RegExp.$2});
        }else{
            $E.container.css({left:UmlEntity.prototype.lastPosition.left,top:UmlEntity.prototype.lastPosition.top});
            UmlEntity.prototype.lastPosition.left+=$E.container.width() + 50;
        
            if( $E.container.height()>UmlEntity.prototype.lastPosition.height)
                UmlEntity.prototype.lastPosition.height = $E.container.height()
        
            if(UmlEntity.prototype.lastPosition.left > 1000){
                UmlEntity.prototype.lastPosition.left = 0;
                UmlEntity.prototype.lastPosition.top += 200;
                UmlEntity.prototype.lastPosition.height = 0;
            }
        }
        
        
        
    };
    
    UmlEntity.prototype.lastPosition = {'left':0,'top':100,'height':0};
    
    
    var UmlAssociation = function($from,$to,$type){  
            
        var $self = this;
        var $_E = {};
        
        $self.getElement = function($name){
            return $_E[$name];
        }
        
        $self.draw = function(){
            if($_E.container)$_E.container.remove();
            
            $cf = $from.getElement('container');
            $ct = $to.getElement('container');
            
            // which one is at the left ?
            if($cf.offset().left > $ct.offset().left){
                $left = $ct.offset().left + $ct.width() /2;
                $width = $cf.offset().left - $ct.offset().left;
                $oppositePath = $ct.offset().top > $cf.offset().top;
            }else{
                $left = $cf.offset().left + $cf.width() /2;
                $width = $ct.offset().left - $cf.offset().left;
                $oppositePath = $ct.offset().top < $cf.offset().top;
            }
            if($cf.offset().top > $ct.offset().top){
                $height = $cf.offset().top - $ct.offset().top //- ($ct.height()/2);
            }else{
                $height = $ct.offset().top - $cf.offset().top //- ($ct.height()/2);
            }
            // if($height<0)
            //   $height = 20;
            
            $top = $ct.offset().top < $cf.offset().top ? $ct.offset().top + 5 /* + $ct.height() /2*/: $cf.offset().top +5 /*+ $cf.height() /2 */;
              
            $_E.container = $('<svg width="'+$width+'"  height="'+$height+'" class="'+($type?$type:'association')+'"><path d="M 0 '+($oppositePath?$height:0)+' L '+$width+' '+($oppositePath?0:$height)+'" ></path></svg>')
            .css({position:'absolute',top:$top,left:$left});
            
            $('body').append($_E.container);
            
        }
        
        $self.remove = function(){
            $_E.container.remove();
        }
        
        // !CONSTRUCTOR
        // don't add twice
        UmlAssociation.prototype.instances[$from.getInfo('class')+"."+$to.getInfo('class')] = this; 
        
        $from.addObserver($self);
        $to.addObserver($self);
    }
    UmlAssociation.prototype.instances = {}
    
    
    $(function() {

         
        
        $umlEntities = {};
        
        $('#button-load').bind('click',function(){
            $xml = $('#xml').val();
            $xmlO = $($.parseXML($('#xml').val().replace('<'+'?xml version="1.0" encoding="UTF-8"?>','')));
            $classes = $xmlO.find('class');
             
            $classes.each(function(){
                $class = $(this);
                var $attributes = {}
                $class.find('attribute').each(function(){
                    $attribute = $(this);
                    $attributes[$attribute.attr('name')] = {
                        required:$attribute.attr('required') == 'true',
                        calculated:$attribute.attr('calculated') == 'true',
                        databaseField:$attribute.attr('databaseField') == 'true',
                        'default':$attribute.attr('default'),
                        html:$attribute.attr('html') == 'true',
                        input:$attribute.attr('input') == 'true',
                        json:$attribute.attr('json') == 'true',
                        searchable:$attribute.attr('searchable') == 'true',
                        type:$attribute.attr('type') == 'true'
                    }
                });
                $umlEntities[$class.attr('name')]= new UmlEntity({
                    name:$class.attr('name'),
                    databaseIdField:"id",
                    databaseTable:"Users",
                    identity:"name,location",
                    inherit:"base",
                    phpClass:"app\\Model\\Venue",
                    attributes:$attributes
                })
            });
        
            $classes.each(function(){
                $class = $(this);
                $class.find('associated').each(function(){
                    if(!UmlAssociation.prototype.instances[$class.attr('name')+"."+$(this).attr('to')])
                        new UmlAssociation($umlEntities[$class.attr('name')],$umlEntities[$(this).attr('to')]).draw();
                });
                if($class.attr('inherit'))
                     new UmlAssociation($umlEntities[$class.attr('name')],$umlEntities[$class.attr('inherit')],'inheritance').draw();
            });   
        });
        
        $('#button-xml').bind('click',function(){
        console.log($xml);
            $('body').append(
                $('<div/>')
                .css({border:'1px solid black','z-index':3,position:'absolute',background:'white'})
                .append(
                    $('<textarea/>')
                    .text($xml)
                )
                .append(
                    $('<input type="button" value="close"/>')
                    .bind('click',function(){$(this).parent().remove()})
                )
            )
        });
    });

    

</script>
<form action="diagram.php" method="post">
    <input type="text" id="xml" name="xml" value="<?php echo isset($_REQUEST['xml']) ? html($_REQUEST['xml']) : ''; ?>">
    <input type="submit">
    <input type="button" value="ok" id="button-load"> 
    <input type="button" value="get XML" id="button-xml">
</form>