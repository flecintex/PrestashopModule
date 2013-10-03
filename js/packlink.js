var tax_products = 0;
var timer = 0;
var data_recieved = "";
$(document).ready(function() {
    timer = setInterval( delivery_option_title_fnt, 50);
});

function callback(data){
    var pl_dat = decode64(pl_data);
    var obj = $.parseJSON(pl_dat[0]);
    data_recieved = data;

    var dp = "";
    $.each(obj, function() {
        dp += '{"weight":"'+(parseFloat(this['weight']).toFixed(2)).toString()+'","width":"'+(parseFloat(this['width']).toFixed(2)).toString()+'","height":"'+(parseFloat(this['height']).toFixed(2)).toString()+'","depth":"'+(parseFloat(this['depth']).toFixed(2)).toString()+'"}, ';
    });
    dp = dp.substr(0, dp.length-2);
    
    var arr_adjust_prices = new Array();
    var arr_prices = new Array();
    $('.price_pl', data).each(function (i){
        var price_pl = $(this).html();
        var pl_shipping_cost = price_pl;
        
        pl_shipping_cost = pl_shipping_cost.substr(0, pl_shipping_cost.indexOf('<br'));
        pl_shipping_cost = parseFloat(pl_shipping_cost.substr(0, pl_shipping_cost.lastIndexOf(" ")));
        var pl_shipping_cost_adjust = pl_shipping_cost *(1+(percentage_adjust/100));
        arr_adjust_prices[i+1]=pl_shipping_cost_adjust.toFixed(2);
        arr_prices[i+1]=pl_shipping_cost.toFixed(2);
        
        // If cost is 0.00 set text to Free!! else set normal cost value.
        if(pl_shipping_cost_adjust.toFixed(2) == 0){
            $(this).html(freeProductTranslation);
        } else {
            $(this).html(pl_shipping_cost_adjust.toFixed(2)+" "+$(this).html().substring($(this).html().indexOf(" ")));
        }
        data = data.replace(price_pl, $(this).html());
    });
    
    $('input.radioBtnStylePL', data).each(function (i){
        var price_pl =  $(this).val();
        var pl_shipping_cost = price_pl.split(",");
        pl_shipping_cost = parseFloat(pl_shipping_cost[2]).toFixed(2);
       
        var pl_shipping_cost_adjust = pl_shipping_cost *(1+(percentage_adjust/100));
        var pl_value_adjust = $(this).val().replace(","+pl_shipping_cost+",", ","+pl_shipping_cost_adjust.toFixed(2)+",");
        
        $(this).val(pl_value_adjust);
        data = data.replace(price_pl, $(this).val());
    });
        
    var pl_inf = dcr();
    if(opc != 1) {
        $('#cart_block_shipping_cost').css("display","none");
        $('#cart_block_shipping_cost').next().css("display","none");
        $('#cart-prices br:lt(1)').css("display","none");
    }

    while (data.toString().indexOf("<address_id>") != -1) data = data.replace("<address_id>",pl_inf[9]);
    while (data.toString().indexOf("<service_id>") != -1) data = data.replace("<service_id>", pl_dat[1]);

    try{ $('#packlink_loader').remove(); } catch(e){}
    $('.delivery_options').append(data);
    $('.order_carrier_content h3').html("SELECCIONE EL TRANSPORTE PARA SU ENVÍO. ");

    $('.delivery_options_address h3').css({"width":"97%", "display":"inline-block"});
    $('.delivery_options_address h3').html("SERVICIO OFRECIDO POR:<span style='float:right'><img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAHMAAAAUCAYAAAC+sgIEAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QzA1M0E4OEE4QzkwMTFFMkFCODVFMkFGOTg4RTlDN0YiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QzA1M0E4OEI4QzkwMTFFMkFCODVFMkFGOTg4RTlDN0YiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpDMDUzQTg4ODhDOTAxMUUyQUI4NUUyQUY5ODhFOUM3RiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpDMDUzQTg4OThDOTAxMUUyQUI4NUUyQUY5ODhFOUM3RiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pi5Dm8AAAA7tSURBVHja7FpnkBzF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' /></span>");
    tax_products = $('#cart_block_tax_cost').html();
    tax_products = tax_products.substr(0, tax_products.lastIndexOf(" "));
    tax_products = parseFloat(tax_products.replace(",", ".").replace(" ", ""));
    
    $('.delivery_option_radio').each(function (i){
        $(this).val($(this).val().replace(","+arr_prices[i+1]+",", ","+arr_adjust_prices[i+1]+","));
    });
    pl_inf = "";
    delCookie(sha1('upSeedPL'+getCookie(sha1('upSeed'))));
    delCookie(sha1('upSeed'));    
    $(".packlinkFirst input").trigger("click");
    updateCart($(".packlinkFirst input"));
}

function unserialize (data) {
  var that = this,
    utf8Overhead = function (chr) {
      var code = chr.charCodeAt(0);
      if (code < 0x0080) {
        return 0;
      }
      if (code < 0x0800) {
        return 1;
      }
      return 2;
    },
    error = function (type, msg, filename, line) {
      throw new that.window[type](msg, filename, line);
    },
    read_until = function (data, offset, stopchr) {
      var i = 2, buf = [], chr = data.slice(offset, offset + 1);

      while (chr != stopchr) {
        if ((i + offset) > data.length) {
          error('Error', 'Invalid');
        }
        buf.push(chr);
        chr = data.slice(offset + (i - 1), offset + i);
        i += 1;
      }
      return [buf.length, buf.join('')];
    },
    read_chrs = function (data, offset, length) {
      var i, chr, buf;

      buf = [];
      for (i = 0; i < length; i++) {
        chr = data.slice(offset + (i - 1), offset + i);
        buf.push(chr);
        length -= utf8Overhead(chr);
      }
      return [buf.length, buf.join('')];
    },
    _unserialize = function (data, offset) {
      var dtype, dataoffset, keyandchrs, keys,
        readdata, readData, ccount, stringlength,
        i, key, kprops, kchrs, vprops, vchrs, value,
        chrs = 0,
        typeconvert = function (x) {
          return x;
        };

      if (!offset) {
        offset = 0;
      }
      dtype = (data.slice(offset, offset + 1)).toLowerCase();

      dataoffset = offset + 2;

      switch (dtype) {
        case 'i':
          typeconvert = function (x) {
            return parseInt(x, 10);
          };
          readData = read_until(data, dataoffset, ';');
          chrs = readData[0];
          readdata = readData[1];
          dataoffset += chrs + 1;
          break;
        case 'b':
          typeconvert = function (x) {
            return parseInt(x, 10) !== 0;
          };
          readData = read_until(data, dataoffset, ';');
          chrs = readData[0];
          readdata = readData[1];
          dataoffset += chrs + 1;
          break;
        case 'd':
          typeconvert = function (x) {
            return parseFloat(x);
          };
          readData = read_until(data, dataoffset, ';');
          chrs = readData[0];
          readdata = readData[1];
          dataoffset += chrs + 1;
          break;
        case 'n':
          readdata = null;
          break;
        case 's':
          ccount = read_until(data, dataoffset, ':');
          chrs = ccount[0];
          stringlength = ccount[1];
          dataoffset += chrs + 2;

          readData = read_chrs(data, dataoffset + 1, parseInt(stringlength, 10));
          chrs = readData[0];
          readdata = readData[1];
          dataoffset += chrs + 2;
          if (chrs != parseInt(stringlength, 10) && chrs != readdata.length) {
            error('SyntaxError', 'String length mismatch');
          }
          break;
        case 'a':
          readdata = {};

          keyandchrs = read_until(data, dataoffset, ':');
          chrs = keyandchrs[0];
          keys = keyandchrs[1];
          dataoffset += chrs + 2;

          for (i = 0; i < parseInt(keys, 10); i++) {
            kprops = _unserialize(data, dataoffset);
            kchrs = kprops[1];
            key = kprops[2];
            dataoffset += kchrs;

            vprops = _unserialize(data, dataoffset);
            vchrs = vprops[1];
            value = vprops[2];
            dataoffset += vchrs;

            readdata[key] = value;
          }

          dataoffset += 1;
          break;
        default:
          error('SyntaxError', 'Unknown / Unhandled data type(s): ' + dtype);
          break;
      }
      return [dtype, dataoffset - offset, typeconvert(readdata)];
    }
  ;

  return _unserialize((data + ''), 0)[2];
}

function serialize (mixed_value) {
  var val, key, okey,
    ktype = '', vals = '', count = 0,
    _utf8Size = function (str) {
      var size = 0,
        i = 0,
        l = str.length,
        code = '';
      for (i = 0; i < l; i++) {
        code = str.charCodeAt(i);
        if (code < 0x0080) {
          size += 1;
        }
        else if (code < 0x0800) {
          size += 2;
        }
        else {
          size += 3;
        }
      }
      return size;
    },
    _getType = function (inp) {
      var match, key, cons, types, type = typeof inp;

      if (type === 'object' && !inp) {
        return 'null';
      }
      if (type === 'object') {
        if (!inp.constructor) {
          return 'object';
        }
        cons = inp.constructor.toString();
        match = cons.match(/(\w+)\(/);
        if (match) {
          cons = match[1].toLowerCase();
        }
        types = ['boolean', 'number', 'string', 'array'];
        for (key in types) {
          if (cons == types[key]) {
            type = types[key];
            break;
          }
        }
      }
      return type;
    },
    type = _getType(mixed_value)
  ;

  switch (type) {
    case 'function':
      val = '';
      break;
    case 'boolean':
      val = 'b:' + (mixed_value ? '1' : '0');
      break;
    case 'number':
      val = (Math.round(mixed_value) == mixed_value ? 'i' : 'd') + ':' + mixed_value;
      break;
    case 'string':
      val = 's:' + _utf8Size(mixed_value) + ':"' + mixed_value + '"';
      break;
    case 'array': case 'object':
      val = 'a';
  /*
        if (type === 'object') {
          var objname = mixed_value.constructor.toString().match(/(\w+)\(\)/);
          if (objname == undefined) {
            return;
          }
          objname[1] = this.serialize(objname[1]);
          val = 'O' + objname[1].substring(1, objname[1].length - 1);
        }
        */

      for (key in mixed_value) {
        if (mixed_value.hasOwnProperty(key)) {
          ktype = _getType(mixed_value[key]);
          if (ktype === 'function') {
            continue;
          }

          okey = (key.match(/^[0-9]+$/) ? parseInt(key, 10) : key);
          vals += this.serialize(okey) + this.serialize(mixed_value[key]);
          count++;
        }
      }
      val += ':' + count + ':{' + vals + '}';
      break;
    case 'undefined':
      // Fall-through
    default:
      // if the JS object has a property which contains a null value, the string cannot be unserialized by PHP
      val = 'N';
      break;
  }
  if (type !== 'object' && type !== 'array') {
    val += ';';
  }
  return val;
}

$(window).load(function(){
   $('.delivery_options').change(function(){
     timer = setInterval( delivery_option_title_fnt, 50);
   });
   try{
       $("a[rel^='prettyPhoto']").prettyPhoto();
   } catch(e){}
   
   try{
       $('#contentSectionsPackLink').click(function(){
            $("a[rel^='prettyPhoto']").prettyPhoto();
       });
   } catch(e){}
   try{
       $('#services_previous').click(function(){
            $("a[rel^='prettyPhoto']").prettyPhoto();
       });
   } catch(e){}
   try{
       $('#services_next').click(function(){
            $("a[rel^='prettyPhoto']").prettyPhoto();
       });
   } catch(e){}
   
});

function delivery_option_title_fnt(){
    var reload = false;
    $('.delivery_option_title').each(function(i){
       if($(this).html() == "Packlink"){ reload = true; }
    })
    if($('.delivery_option_title').html() == null) { clearInterval(timer); }
    
    
    if(reload){
        clearInterval(timer);
       
        var pl_dat = decode64(pl_data);
        
        var obj = $.parseJSON(pl_dat[0]);

        var dp = "";
        $.each(obj, function() {
            dp += '{"weight":"'+(parseFloat(this['weight']).toFixed(2)).toString()+'","width":"'+(parseFloat(this['width']).toFixed(2)).toString()+'","height":"'+(parseFloat(this['height']).toFixed(2)).toString()+'","depth":"'+(parseFloat(this['depth']).toFixed(2)).toString()+'"}, ';
        });
        dp = dp.substr(0, dp.length-2);
        
        var pl_inf = dcr();
       
        var layer_pl  = "<div id='packlink_loader' style='height:30px; padding:20px !important; text-align:center; margin:0 auto;'><img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAHMAAAAUCAYAAAC+sgIEAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QzA1M0E4OEE4QzkwMTFFMkFCODVFMkFGOTg4RTlDN0YiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QzA1M0E4OEI4QzkwMTFFMkFCODVFMkFGOTg4RTlDN0YiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpDMDUzQTg4ODhDOTAxMUUyQUI4NUUyQUY5ODhFOUM3RiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpDMDUzQTg4OThDOTAxMUUyQUI4NUUyQUY5ODhFOUM3RiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pi5Dm8AAAA7tSURBVHja7FpnkBzF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' />";
            layer_pl += "<br/><img src='data:image/gif;base64,R0lGODlhgAAPAKIAALCvsMPCwz8/PwAAAPv6+wAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQECgAAACwAAAAAgAAPAAAD50ixS/6sPRfDpPGqfKv2HTeBowiZGLORq1lJqfuW7Gud9YzLud3zQNVOGCO2jDZaEHZk+nRFJ7R5i1apSuQ0OZT+nleuNetdhrfob1kLXrvPariZLGfPuz66Hr8f8/9+gVh4YoOChYhpd4eKdgwAkJEAE5KRlJWTD5iZDpuXlZ+SoZaamKOQp5wEm56loK6isKSdprKotqqttK+7sb2zq6y8wcO6xL7HwMbLtb+3zrnNycKp1bjW0NjT0cXSzMLK3uLd5Mjf5uPo5eDa5+Hrz9vt6e/qosO/GvjJ+sj5F/sC+uMHcCCoBAAh+QQECgAAACwAAAAABwAPAAADEUiyq/wwyknjuDjrzfsmGpEAACH5BAQKAAAALAsAAAAHAA8AAAMRSLKr/DDKSeO4OOvN+yYakQAAIfkEBAoAAAAsFgAAAAcADwAAAxFIsqv8MMpJ47g46837JhqRAAAh+QQECgAAACwhAAAABwAPAAADEUiyq/wwyknjuDjrzfsmGpEAACH5BAQKAAAALCwAAAAHAA8AAAMRSLKr/DDKSeO4OOvN+yYakQAAIfkEBAoAAAAsNwAAAAcADwAAAxFIsqv8MMpJ47g46837JhqRAAAh+QQECgAAACxCAAAABwAPAAADEUiyq/wwyknjuDjrzfsmGpEAACH5BAQKAAAALE0AAAAHAA8AAAMRSLKr/DDKSeO4OOvN+yYakQAAIfkEBAoAAAAsWAAAAAcADwAAAxFIsqv8MMpJ47g46837JhqRAAAh+QQECgAAACxjAAAABwAPAAADEUiyq/wwyknjuDjrzfsmGpEAACH5BAQKAAAALG4AAAAHAA8AAAMRSLKr/DDKSeO4OOvN+yYakQAAIfkEBAoAAAAseQAAAAcADwAAAxFIsqv8MMpJ47g46837JhqRAAA7' />";
            layer_pl += "<br/>Espere, por favor...</div>"
            
        $('.delivery_options').css({"padding-bottom":"20px", "border":"0 none"});
        $(' h3', $('.delivery_options').parent()).css("border-radius", "5px");
        
        var ctimeSeed = new Date().getTime() / 1000;
        var stimeSeed = parseInt(ctimeSeed, 10);
            ctimeSeed = (Math.round((ctimeSeed - stimeSeed) * 1000) / 1000) + ' ' + stimeSeed;
        
        if(pl_inf == null || pl_inf == ""){
            pl_inf = "";
            try { delCookie(sha1('upSeedPL'+getCookie(sha1('upSeed'))));} catch (e) {}
            try { delCookie(sha1('upSeed'));} catch (e) {}
            return "";
        }
        
        $.ajax({
            async:true,
            beforeSend: function ( xhr ) {
                try{
                    $('.delivery_options .delivery_option').each(function (){
                        var curItem = $(this);
                        if($('.delivery_option_title', this).html()=="Packlink"){
                            curItem.html(layer_pl);
                        }
                    });
                } catch(e) {}
            },
            url:pl_inf[10],
            crossDomain:true,
            dataType:"jsonp",
            contentType: "application/json; charset=utf-8",
            cache: 'false', 
            data:{
            username: pl_inf[11],
            password:sha1(pl_inf[12]+pl_inf[19]+ctimeSeed), 
            seed:ctimeSeed,
            apikey:pl_inf[13], request_format:"json", response_format:"prestashop",
            charset:"UTF-8", language:"es", query:"get/quotes",
            data:'{"quotes":{"choose_service":"'+pl_inf[17]+'","cp_source":"'+pl_inf[1].toString()+'","iso_source":"'+pl_inf[7].toLowerCase()+'","cp_target":"'+pl_inf[0].toString()+'","iso_target":"'+pl_inf[6].toLowerCase()+'","packlist":['+dp+'],"town_source":"false","town_target":"false"}}' 
            },
            success:callback,
            fail: function(e, t){
                alert(JSON.stringify(e)+" "+t);
            }
        });
    }
}

function checkOrderCarrier(){
    var carrier_id = 0;
    var test = acceptCGV();
    if (!test){ return false; }
    else{
        var radios = document.getElementsByName("input");
        for( i = 0; i < radios.length; i++ ) {
            if( radios[i].type == "radio" && radios[i].checked ){
                 carrier_id = radios[i].value;
            }
        }
        return true;
    }
}
function updateCart(item){
    $('#cart_block_shipping_cost').css("display","");
    $('#cart_block_shipping_cost').next().css("display","");
    $('#cart-prices br:lt(1)').css("display","");

    $('div', $(item).parent().parent()).each(function() {
       $(this).removeClass("select_bg_pl"); 
    });

    var pl_total = 0;
    var pl_shipping_cost = 0;
    var pl_shipping_iva = parseFloat($('.price_pl_iva_h', $(item).parent()).html());
    $('#cart_block_list span.price').each(function() {
        if($(this).attr("class").toLowerCase() == "price"){
            var pl_item = $(this).html().substr(0, $(this).html().lastIndexOf(" "));
                pl_item = parseFloat(pl_item.replace(",", ".").replace(" ", ""));

                pl_total += pl_item;

        } else if($(this).attr("class").toLowerCase().indexOf("shipping_cost") != -1){
              pl_shipping_cost = $('.price_pl', $(item).parent()).html();
              pl_shipping_cost = pl_shipping_cost.substr(0, pl_shipping_cost.indexOf('<br'));
              
              if(pl_shipping_cost == ""){
                  pl_shipping_cost = 0;
              } else {
                pl_shipping_cost = parseFloat(pl_shipping_cost.substr(0, pl_shipping_cost.lastIndexOf(" ")))*(1+pl_shipping_iva);
              }
              pl_total += pl_shipping_cost;
        }
    });

    pl_total = pl_total.toFixed(2);
    pl_shipping_cost = pl_shipping_cost.toFixed(2);
    var tax_cost = pl_shipping_cost/(1+pl_shipping_iva).toFixed(2);
    if(pl_shipping_cost == 0){
        tax_cost = parseFloat(tax_products);
    } else {
        tax_cost = (parseFloat(tax_products)+parseFloat(pl_shipping_cost) - parseFloat(tax_cost)).toFixed(2);
    }
    $(item).parent().addClass("select_bg_pl");
    
    if(pl_shipping_cost == 0){
        $('#cart_block_shipping_cost').html(freeProductTranslation);
    } else {
        $('#cart_block_shipping_cost').html(pl_shipping_cost.toString().replace(".",",").replace(/\B(?=(\d{3})+(?!\d))/g, " ")+" €");
    }
    $('#cart_block_tax_cost').html(tax_cost.toString().replace(".",",").replace(/\B(?=(\d{3})+(?!\d))/g, " ")+" €");
    $('#cart_block_total').html((pl_total).toString().replace(".",",").replace(/\B(?=(\d{3})+(?!\d))/g, " ")+" €");

    try{
        if(opc == 1) {
            var path = window.location.pathname.replace(/\\/g,'/').replace(/\/[^\/]*$/, '')+"/";
            var path_modules = module_dir.substr(module_dir.indexOf(path)+path.length).replace("\\", "/")+"packlink/"; 
            var pl_inf = dcr();
           
            $.post(path_modules+"test.php", { val: $(item).val(), ida:pl_inf[9], idc:pl_inf[14], idp:pl_inf[15]});
        }
    } catch (e){
        
    }
}
    var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

    function encode64(input) {
     input = escape(input);
     var output = "";
     var chr1, chr2, chr3 = "";
     var enc1, enc2, enc3, enc4 = "";
     var i = 0;

     do {
        chr1 = input.charCodeAt(i++);
        chr2 = input.charCodeAt(i++);
        chr3 = input.charCodeAt(i++);

        enc1 = chr1 >> 2;
        enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
        enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
        enc4 = chr3 & 63;

        if (isNaN(chr2)) {
           enc3 = enc4 = 64;
        } else if (isNaN(chr3)) {
           enc4 = 64;
        }

        output = output +
           keyStr.charAt(enc1) +
           keyStr.charAt(enc2) +
           keyStr.charAt(enc3) +
           keyStr.charAt(enc4);
        chr1 = chr2 = chr3 = "";
        enc1 = enc2 = enc3 = enc4 = "";
     } while (i < input.length);

     return output;
  }

  function decode64(input) {
     var output = "";
     var chr1, chr2, chr3 = "";
     var enc1, enc2, enc3, enc4 = "";
     var i = 0;

     // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
     var base64test = /[^A-Za-z0-9\+\/\=]/g;
     if (base64test.exec(input)) {
        alert("There were invalid base64 characters in the input text.\n" +
              "Valid base64 characters are A-Z, a-z, 0-9, '+', '/',and '='\n" +
              "Expect errors in decoding.");
     }
     input = input.substr(0, input.length-1).replace(/[^A-Za-z0-9\+\/\=]/g, "");

     do {
        enc1 = keyStr.indexOf(input.charAt(i++));
        enc2 = keyStr.indexOf(input.charAt(i++));
        enc3 = keyStr.indexOf(input.charAt(i++));
        enc4 = keyStr.indexOf(input.charAt(i++));

        chr1 = (enc1 << 2) | (enc2 >> 4);
        chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
        chr3 = ((enc3 & 3) << 6) | enc4;

        output = output + String.fromCharCode(chr1);

        if (enc3 != 64) {
           output = output + String.fromCharCode(chr2);
        }
        if (enc4 != 64) {
           output = output + String.fromCharCode(chr3);
        }

        chr1 = chr2 = chr3 = "";
        enc1 = enc2 = enc3 = enc4 = "";

     } while (i < input.length);

     return unescape(output).split("|");
  }
  
  function pad (n, length){
    var str = (n > 0 ? n : -n) + "";
    var zeros = "";
    for (var i = length - str.length; i > 0; i--)
        zeros += "0";
    zeros += str;
    return n >= 0 ? zeros : "-" + zeros;
}
  
  /*
   * SCRIPTS 
   **/
  
  $('#sectionsPackLink span').click(function (){
    var index = $(this).index();
    $('#sectionsPackLink span').each(function (i){
        $(this).removeClass('selected');

        if(index == i){
            $("#contentSectionsPackLink fieldset:nth-child("+(i+1)+")", $(this).parent().parent()).css("display", "");
        } else {
            $("#contentSectionsPackLink fieldset:nth-child("+(i+1)+")", $(this).parent().parent()).css("display", "none");
        }
    });
    $(this).addClass('selected');
});

// --------------------------------------
// Function SHA1 compatible with PHP
// --------------------------------------

function sha1 (msg) {
	 
	function rotate_left(n,s) {
		var t4 = ( n<<s ) | (n>>>(32-s));
		return t4;
	};
 
	function lsb_hex(val) {
		var str="";
		var i;
		var vh;
		var vl;
 
		for( i=0; i<=6; i+=2 ) {
			vh = (val>>>(i*4+4))&0x0f;
			vl = (val>>>(i*4))&0x0f;
			str += vh.toString(16) + vl.toString(16);
		}
		return str;
	};
 
	function cvt_hex(val) {
		var str="";
		var i;
		var v;
 
		for( i=7; i>=0; i-- ) {
			v = (val>>>(i*4))&0x0f;
			str += v.toString(16);
		}
		return str;
	};
 
 
	function Utf8Encode(string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
 
		for (var n = 0; n < string.length; n++) {
 
			var c = string.charCodeAt(n);
 
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
 
		}
 
		return utftext;
	};
 
	var blockstart;
	var i, j;
	var W = new Array(80);
	var H0 = 0x67452301;
	var H1 = 0xEFCDAB89;
	var H2 = 0x98BADCFE;
	var H3 = 0x10325476;
	var H4 = 0xC3D2E1F0;
	var A, B, C, D, E;
	var temp;
 
	msg = Utf8Encode(msg);
 
	var msg_len = msg.length;
 
	var word_array = new Array();
	for( i=0; i<msg_len-3; i+=4 ) {
		j = msg.charCodeAt(i)<<24 | msg.charCodeAt(i+1)<<16 |
		msg.charCodeAt(i+2)<<8 | msg.charCodeAt(i+3);
		word_array.push( j );
	}
 
	switch( msg_len % 4 ) {
		case 0:
			i = 0x080000000;
		break;
		case 1:
			i = msg.charCodeAt(msg_len-1)<<24 | 0x0800000;
		break;
 
		case 2:
			i = msg.charCodeAt(msg_len-2)<<24 | msg.charCodeAt(msg_len-1)<<16 | 0x08000;
		break;
 
		case 3:
			i = msg.charCodeAt(msg_len-3)<<24 | msg.charCodeAt(msg_len-2)<<16 | msg.charCodeAt(msg_len-1)<<8	| 0x80;
		break;
	}
	word_array.push( i );
	while( (word_array.length % 16) != 14 ) word_array.push( 0 );
	word_array.push( msg_len>>>29 );
	word_array.push( (msg_len<<3)&0x0ffffffff );
	for ( blockstart=0; blockstart<word_array.length; blockstart+=16 ) {
		for( i=0; i<16; i++ ) W[i] = word_array[blockstart+i];
		for( i=16; i<=79; i++ ) W[i] = rotate_left(W[i-3] ^ W[i-8] ^ W[i-14] ^ W[i-16], 1);
		A = H0;
		B = H1;
		C = H2;
		D = H3;
		E = H4;
		for( i= 0; i<=19; i++ ) {
			temp = (rotate_left(A,5) + ((B&C) | (~B&D)) + E + W[i] + 0x5A827999) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B,30);
			B = A;
			A = temp;
		}
		for( i=20; i<=39; i++ ) {
			temp = (rotate_left(A,5) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B,30);
			B = A;
			A = temp;
		}
		for( i=40; i<=59; i++ ) {
			temp = (rotate_left(A,5) + ((B&C) | (B&D) | (C&D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B,30);
			B = A;
			A = temp;
		}
		for( i=60; i<=79; i++ ) {
			temp = (rotate_left(A,5) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B,30);
			B = A;
			A = temp;
		}
		H0 = (H0 + A) & 0x0ffffffff;
		H1 = (H1 + B) & 0x0ffffffff;
		H2 = (H2 + C) & 0x0ffffffff;
		H3 = (H3 + D) & 0x0ffffffff;
		H4 = (H4 + E) & 0x0ffffffff;
	}

	var temp = cvt_hex(H0) + cvt_hex(H1) + cvt_hex(H2) + cvt_hex(H3) + cvt_hex(H4);
	return temp.toLowerCase();
}

// --------------------------------------
// Cookies Management
// --------------------------------------

function getCookie(nombre){
    var nombreCookie, valorCookie, cookie = null, cookies = document.cookie.split(';');
    for (i=0; i<cookies.length; i++){
      valorCookie = cookies[i].substr(cookies[i].indexOf('=') + 1);
      nombreCookie = cookies[i].substr(0,cookies[i].indexOf('=')).replace(/^\s+|\s+$/g, '');
      if (nombreCookie == nombre)
        cookie = unescape(valorCookie);
    }
    
    return cookie;
}

function setCookie(nombre, valor, tiempo){
    var fecha = new Date();
    fecha.setTime(fecha.getTime() + tiempo);
    document.cookie = nombre + ' = ' + escape(valor) + ((tiempo == null) ? '' : '; expires = ' + fecha.toGMTString()) + '; path=/';
}

var delCookie = function(name) { document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;'; };

function decrypt(string, key) {
    var result = '';
    if(string == null || string == ""){
        try { delCookie(sha1('upSeedPL'+getCookie(sha1('upSeed'))));} catch (e) {}
        try { delCookie(sha1('upSeed'));} catch (e) {}
        return "";
    }
    
    var string = base64_decode(str_pad(strtr(string, '-_', '+/'), string.lenth % 4, '='));
    for(var i=0; i< string.length; i++) {
       var chr1 = string.substr(i, 1);
       var keychar = key.substr((i % key.length)-1, 1);
       chr1 = chr2(ord2(chr1)-ord2(keychar));
       result +=chr1;
    }
    return result;
}

function base64_decode (data) {
  var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
  var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
    ac = 0,
    dec = "",
    tmp_arr = [];

  if (!data) {
    return data;
  }

  data += '';

  do { 
    h1 = b64.indexOf(data.charAt(i++));
    h2 = b64.indexOf(data.charAt(i++));
    h3 = b64.indexOf(data.charAt(i++));
    h4 = b64.indexOf(data.charAt(i++));

    bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

    o1 = bits >> 16 & 0xff;
    o2 = bits >> 8 & 0xff;
    o3 = bits & 0xff;

    if (h3 == 64) {
      tmp_arr[ac++] = String.fromCharCode(o1);
    } else if (h4 == 64) {
      tmp_arr[ac++] = String.fromCharCode(o1, o2);
    } else {
      tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
    }
  } while (i < data.length);

  dec = tmp_arr.join('');

  return dec;
}

function str_pad (input, pad_length, pad_string, pad_type) {
  var half = '',
    pad_to_go;

  var str_pad_repeater = function (s, len) {
    var collect = '',
      i;

    while (collect.length < len) {
      collect += s;
    }
    collect = collect.substr(0, len);

    return collect;
  };

  input += '';
  pad_string = pad_string !== undefined ? pad_string : ' ';

  if (pad_type !== 'STR_PAD_LEFT' && pad_type !== 'STR_PAD_RIGHT' && pad_type !== 'STR_PAD_BOTH') {
    pad_type = 'STR_PAD_RIGHT';
  }
  if ((pad_to_go = pad_length - input.length) > 0) {
    if (pad_type === 'STR_PAD_LEFT') {
      input = str_pad_repeater(pad_string, pad_to_go) + input;
    } else if (pad_type === 'STR_PAD_RIGHT') {
      input = input + str_pad_repeater(pad_string, pad_to_go);
    } else if (pad_type === 'STR_PAD_BOTH') {
      half = str_pad_repeater(pad_string, Math.ceil(pad_to_go / 2));
      input = half + input + half;
      input = input.substr(0, pad_length);
    }
  }

  return input;
}

function chr2 (codePt) {
  if (codePt > 0xFFFF) { 
    codePt -= 0x10000;
    return String.fromCharCode(0xD800 + (codePt >> 10), 0xDC00 + (codePt & 0x3FF));
  }
  return String.fromCharCode(codePt);
}

function ord2 (string) {
  var str = string + '',
    code = str.charCodeAt(0);
  if (0xD800 <= code && code <= 0xDBFF) { 
    var hi = code;
    if (str.length === 1) {
      return code; 
    }
    var low = str.charCodeAt(1);
    return ((hi - 0xD800) * 0x400) + (low - 0xDC00) + 0x10000;
  }
  if (0xDC00 <= code && code <= 0xDFFF) { 
    return code; 
  }
  return code;
}
function dcr(){var aux = decrypt(getCookie(sha1('upSeedPL'+getCookie(sha1('upSeed')))), getCookie(sha1('upSeed')));return aux.substr(0, aux.toString().length-1).split("|");}
function strtr (str, from, to) {
    var fr = '',
    i = 0,
    j = 0,
    lenStr = 0,
    lenFrom = 0,
    tmpStrictForIn = false,
    fromTypeStr = '',
    toTypeStr = '',
    istr = '';
  var tmpFrom = [];
  var tmpTo = [];
  var ret = '';
  var match = false;

  if (typeof from === 'object') {
    tmpStrictForIn = this.ini_set('phpjs.strictForIn', false);
    from = this.krsort(from);
    this.ini_set('phpjs.strictForIn', tmpStrictForIn);

    for (fr in from) {
      if (from.hasOwnProperty(fr)) {
        tmpFrom.push(fr);
        tmpTo.push(from[fr]);
      }
    }

    from = tmpFrom;
    to = tmpTo;
  }
  if(str == null){
    try { delCookie(sha1('upSeedPL'+getCookie(sha1('upSeed'))));} catch (e) {}
    try { delCookie(sha1('upSeed'));} catch (e) {}
    return "";
}
  lenStr = str.toString().length;
   lenFrom = from.toString().length;
  fromTypeStr = typeof from === 'string';
  toTypeStr = typeof to === 'string';

  for (i = 0; i < lenStr; i++) {
    match = false;
    if (fromTypeStr) {
      istr = str.toString().charAt(i);
      for (j = 0; j < lenFrom; j++) {
        if (istr == from.charAt(j)) {
          match = true;
          break;
        }
      }
    } else {
      for (j = 0; j < lenFrom; j++) {
        if (str.substr(i, from[j].length) == from[j]) {
          match = true;
          i = (i + from[j].length) - 1;
          break;
        }
      }
    }
    if (match) {
      ret += toTypeStr ? to.charAt(j) : to[j];
    } else {
      ret += str.toString().charAt(i);
    }
  }

  return ret;
}