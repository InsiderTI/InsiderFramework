$(document).ready(function(){
    // Variável que armazena a última URL acessada via ajax
    lastajaxhrefvisited="";

    // Variável de monitoramento de URL
    laststatehistory=null;

    // Desabilitando seleção em elementos da classe 'noselect'
    $('.noselect').attr('unselectable', 'on').css('user-select', 'none').on('selectstart', function(){ return false; }).on('mousedown', function(){return false;});
});

// Monitorando a mudança de URL
window.onpopstate = function(event){
    // Se o último estado da URL for diferente
    if (JSON.stringify(event.state) !== laststatehistory){
        // Grava o estado atual
        laststatehistory=JSON.stringify(event.state);

        // Recarrega a página
        location.reload();
    }
};

/**
 *   @author Marcello Costa
 *
 *   Substitui todas as ocorrências de uma string em uma sentença
 *
 *   @param  {String}  find       O que deve ser encontrado
 *   @param  {String}  replace    O que entrará no lugar da string encontrada
 *   @param  {String}  str        String onde deverá ser feita a busca
 *
 *   @returns  {Void}
*/
function ReplaceAll(find, replace, str){
    return str.split(find).join(replace);
};

/**
 *   @author Marcello Costa
 *
 *   Verifica se uma variável é JSON
 *
 *   @param  {*}  val    Variável a ser testada
 *
 *   @returns  {Bool}  Resultado da função
*/
function IsJSON(val){
    try {
        JSON.parse(val);
    } catch (e) {
        return false;
    }
    return true;
};

/**
 *   @author Marcello Costa
 *
 *   Retorna em um array o resultado devolvido da renderização de
 *   uma view (código + css + js). O array fica então da seguinte
 *   forma: array['css'], array['script'], array['code']
 *
 *   @param  {String}  resultview    Resultado devolvido de uma requisição ajax à uma view
 *
 *   @returns  {Array}  Resultado separado em um array
*/
function ParseJSONView(resultview){
    // Se é um JSON
    if (IsJSON(resultview)){
        // Efetua o parse do resultado
        resultjson=JSON.parse(resultview);

        // Tratando CSS
        resultjson['css']=ReplaceAll('\\n\\', '', resultjson['css']);

        // Tratando SCRIPT
        resultjson['script']=ReplaceAll('\\n\\', '', resultjson['script']);

        // Retornando valores
        return resultjson;
    }

    // Se não é um JSON, não pode ser tratado por esta função
    else{
        return false;
    }
};

/**
 *   @author Marcello Costa
 *
 *   Atualiza o css da página
 *
 *   @param  {String}  css    Código css a ser inserido
 *
 *   @returns  {Void}
*/
function updateCSSOfPage(css){
    $('head').append(css);
};

/**
 *   @author Marcello Costa
 *
 *   Atualiza o js da página
 *
 *   @param  {String}  js    Código js a ser inserido
 *
 *   @returns  {Void}
*/
function updateJSOfPage(js){
    $('head').append(js);
};

/**
 *   @author Marcello Costa
 *
 *   Para o script durante um determinado tempo (em milisegundos)
 *
 *   @param  {milliseconds}  Tempo que o script ficará parado
 *
 *   @returns  {Void}
*/
function Sleep(milliseconds){
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
        if ((new Date().getTime() - start) > milliseconds){
          break;
        }
    }
};

/**
 *   @author Marcello Costa
 *
 *   Atualiza os dados de um cookie
 *
 *   @param  {String}  cookie    Nome do cookie
 *   @param  {String}  prop      Nome da propriedade a ser atualizada
 *   @param  {String}  newdata   Dados que serão inseridos no cookie
 *
 *   @returns  {Void}
*/
function UpdateDataCookie(cookie,prop,newdata){
    // Recuperando informações
    $.ajax({
        type: "POST",
        url: '/sys/getdatacookie/'+cookie+'/true',
        data: '/sys/getdatacookie/'+cookie+'/true',
        success: function (result){
            // Se o cookie existir e contiver informações
            if (result.trim().length !== 0){
                 // Formatando JSON
                var find = '&quot;';
                var re = new RegExp(find, 'g');
                resultf = result.replace(re, '"');

                // Transformando em um objeto JSON
                cookievalue=JSON.parse(resultf);
            }

            // Se o cookie não existir ou não contiver informações
            else{
                // Cria um objeto JSON vazio
                cookievalue=JSON.parse('{}');
            }

            // Adicionando novo valor ao array
            cookievalue[prop]=newdata;

            // Tratando as barras do JSON
            cookievalue=JSON.stringify(cookievalue);

            // Nova variável de cookie
            newcookievalue="";

            // Para cada letra da string
            for (var i = 0, len = cookievalue.length; i < len; i++){
                // Se existir uma barra e no caractere anterior não existir uma barra invertida
                if (cookievalue[i] === "/" && (((cookievalue[i-1]).length !== 0) && cookievalue[i-1]) !== "\\"){
                    // Adiciona a barra invertida
                    newcookievalue+="\\"+cookievalue[i];
                }
                else{
                    newcookievalue+=cookievalue[i];
                }
            }

            // Armazendo informações
            $.ajax({
                type: "POST",
                url: '/sys/setdatacookie/'+cookie+'/'+newcookievalue+'/true',
                data: '/sys/setdatacookie/'+cookie+'/'+newcookievalue+'/true',
                success: function (result){
                }
            });
        }
    });

    return true;
};

/**
 *   @author Marcello Costa
 *
 *   Recupera informações de um cookie
 *
 *   @param  {String}  cookiename    Nome do cookie
 *   @param  {String}  prop          Nome da propriedade a ser atualizada
 *   @param  {Bool}    jsondata      Se true, o retorno da função será um JSON
 *
 *   @returns  {*}  Informações recuperadas do cookie
*/
function GetDataCookie(cookiename, prop, jsondata){
    var cookiedata="";

    $.ajax({
        type: "POST",
        async: false,
        url: '/sys/getdatacookie/namecookie/'+cookiename+'/jsondata/'+jsondata,
        data: '/sys/getdatacookie/namecookie/'+cookiename+'/jsondata/'+jsondata,
        success: function (result){
            // Se o cookie existir e contiver informações, utiliza as mesmas
            if (result.trim().length !== 0){
                // Se for JSON
                if (jsondata === true && result != null){
                    // Formatando JSON
                    var find = '&quot;';
                    var re = new RegExp(find, 'g');
                    resultf = result.replace(re, '"');
                    cookiedatajson=JSON.parse(resultf);

                    // Se existir a propriedade
                    if (typeof cookiedatajson[prop] !== 'undefined'){
                        cookiedata=cookiedatajson[prop];
                    }
                }

                // Se não for JSON
                else{
                    // Retorna o resultado
                    cookiedata=result;
                }
            }
        }
    });

    return cookiedata;
};

/**
 *   @author Marcello Costa
 *
 *   Função para apagar um cookie do navegador (não funciona com cookies de sessão)
 *
 *   @param  {String}  cookiename    Nome do cookie
 *
 *   @returns  {Void}
*/
function DeleteCookie(cookiename){
    document.cookie = cookiename + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
};

/**
 *   @author Marcello Costa
 *
 *   Cria/atualiza os dados de um cookie com javascript puro
 *
 *   @param  {String}  name    Nome do cookie
 *   @param  {String}  value   Valor do cookie
 *   @param  {Int}     days    Validade do cookie (em dias)
 *
 *   @returns  {Void}
*/
var UpdateDataCookieJS = function(name, value, days) {
    var expires;
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    }
    else {
        expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
};

/**
 *   @author Marcello Costa
 *
 *   Recupera os dados de um cookie com javascript puro
 *
 *   @param  {String}  c_name    Nome do cookie
 *
 *   @returns  {Void}
*/
function GetDataCookieJS(c_name) {
    if (document.cookie.length > 0) {
        c_start = document.cookie.indexOf(c_name + "=");
        if (c_start != -1) {
            c_start = c_start + c_name.length + 1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) {
                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return "";
};

/**
 *   @author Marcello Costa
 *
 *   Converte dados para serem enviados via URL
 *
 *   @param  {String}  data    Nome do cookie
 *   @param  {Bool}    json    Converter dados recebidos para JSON
 *
 *   @returns  {String}  String convertida para json
*/
function ConvertDataToPost(data, json){

    // Convertendo ou não dados para JSON
    if (json === true){
        newdatatmp=JSON.stringify(data);
    }
    else{
        newdatatmp=data;
    }

    // Substituindo barras "/" por "\/"
    newdata=ReplaceAll('/','\\/', newdatatmp);

    // Retornando dados tratados
    return newdata;
};

/**
 *   @author Marcello Costa
 *
 *   Retorna os parâmetros GET da requisição atual
 *
 *   @returns  {Array}  Array associativo com as chaves e valores GET
*/
function getGetParams(){
    urlgets=window.location.search.replace("?", "").split("&");
    params = {};

    $.each(urlgets, function(key, value){
        if (value !== ""){
            keyvalue=value.split("=");
            truekey=keyvalue[0];
            truevalue=keyvalue[1];
            params[truekey]=truevalue;
        }
    });

    return params;
};

/**
 *   @author Marcello Costa
 *
 *   Conta caracteres de um elemento e devolva a informação em outro
 *
 *   @param  {Object}  obj           Objecto que contém o valor atual da div
 *                                   (use onkeyup="CountChar(this)" no elemento)
 *   @param  {String}  elemtoinfo    Elemento que receberá a informação
 *   @param  {Int}     limit         Número máximo de caracteres
 *
 *   @returns  {Void}
*/
function CountChar(obj, elemtoinfo, limit){
    // Tamanho do campo
    var len = obj.value.length;

    // Se o tamanho estiver fora do limite
    if (len >= limit){
        obj.value = obj.value.substring(0, limit);

        // Modificando texto
        $('#'+elemtoinfo).text(limit+'/'+limit);
    }

    // Se ainda estiver dentro do limite
    else{
        // Modificando texto
        $('#'+elemtoinfo).text(len+'/'+limit);
    }
};

/**
 *   @author Marcello Costa
 *
 *   Converte uma timestamp para o formato PT-BR
 *
 *   @param  {String}  timestamp    desc
 *
 *   @returns  {Void}
*/
function ConvertTimeStamp(timestamp){
    timestamp_tmp=timestamp.split('-');

    ano=timestamp_tmp[0];
    mes=timestamp_tmp[1];
    diahora=timestamp_tmp[2];

    dia_tmp=diahora.split(' ');
    dia=dia_tmp[0];
    hora=dia_tmp[1];

    return (dia+"/"+mes+"/"+ano+" "+hora);
};

/**
 *   @author Marcello Costa
 *
 *   Compara duas strings que são datas.
 *   Se a data inicial é maior que a final, retorna false.
 *
 *   @param  {String}  dataInicial    Data Inicial
 *   @param  {String}  dataFinal      Data Final
 *   @param  {String}  format         Formato das datas
 *
 *   @returns  {Bool}
*/
function comparaDatas(dataInicial, dataFinal, format="YYYY-MM-DD") {
    data_1=moment(dataInicial, format);
    data_2=moment(dataFinal, format);

    if (data_1 == 'Invalid Date'){
       throw new Error("Invalid Date Format");
    }

    if (data_1 === data_2){
        return true;
    }
    if (data_1 < data_2){
        return true;
    }
    else {
        return false;
    }
}

/* Strip de tags html (primeiro método) */
/**
 *   @author Martijn
 *   @see {http://stackoverflow.com/questions/5499078/fastest-method-to-escape-html-tags-as-html-entities}
 *
 *   Remove tags html de uma string
 *
 *   @param  {String}  str    String a ser tratada
 *
 *   @returns  {String}  String tratada
*/
var tagsToReplace = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;'
};

function replaceTag(tag){
    return tagsToReplace[tag] || tag;
};

function SafeTagsReplace(str) {
    return str.replace(/[&<>]/g, replaceTag);
};

/* Strip de tags html (segundo método) */
/**
 *   @author VyvIT, Robert K
 *   @see {http://stackoverflow.com/questions/5796718/html-entity-decode}
 *
 *   Remove tags html de uma string. Para usar faça: decodeEntities('<img src=fake onerror="prompt(1)">');
 *
 *   @param  {String}  str    String a ser tratada
 *
 *   @returns  {String}  String tratada
*/
var decodeEntities = (function () {
    //create a new html document (doesn't execute script tags in child elements)
    var doc = document.implementation.createHTMLDocument("");
    var element = doc.createElement('div');

    function getText(str) {
        element.innerHTML = str;
        str = element.textContent;
        element.textContent = '';
        return str;
    };

    function decodeHTMLEntities(str) {
        if (str && typeof str === 'string') {
            var x = getText(str);
            while (str !== x) {
                str = x;
                x = getText(x);
            }
            return x;
        }
    };
    return decodeHTMLEntities;
})();
/* Fim do Strip de tags html */