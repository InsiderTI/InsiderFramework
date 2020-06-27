document.addEventListener("DOMContentLoaded", function(event) {
  laststatehistory = null
})

window.onpopstate = function(event) {
  if (JSON.stringify(event.state) !== laststatehistory) {
    laststatehistory = JSON.stringify(event.state)
    location.reload()
  }
}

/**
 *   @author Marcello Costa
 *
 *   Replaces all occurrences of a string in a sentence
 *
 *   @param  {String}  find       What should be found
 *   @param  {String}  replace    What will replace the found string
 *   @param  {String}  str        String where to search
 *
 *   @returns  {Void}
 */
function replaceAll(find, replace, str) {
  return str.split(find).join(replace)
}

/**
 *   @author Marcello Costa
 *
 *   Checks whether a variable is JSON
 *
 *   @param  {*}  val    Variable to be tested
 *
 *   @returns  {Bool}  Function result
 */
function isJSON(val) {
  try {
    JSON.parse(val)
  } catch (e) {
    return false
  }
  return true
}

/**
 *   @author Marcello Costa
 *
 * Returns in an array the result returned from rendering
 * a view (code + css + js). The array is then as follows
 * form: array ['css'], array ['script'], array ['code']
 *
 *   @param  {String}  resultview    Result returned from an ajax request to a view
 *
 *   @returns  {Array}  Separate result in an array
 */
function parseJSONView(resultview) {
  if (isJSON(resultview)) {
    resultjson = JSON.parse(resultview)
    resultjson["css"] = replaceAll("\\n\\", "", resultjson["css"])
    resultjson["script"] = replaceAll("\\n\\", "", resultjson["script"])
    return resultjson
  }
  return false
}

/**
 *   @author Marcello Costa
 *
 *   Stops the script for a specified time (in milliseconds)
 *
 *   @param  {milliseconds}  Time the script will be stopped
 *
 *   @returns  {Void}
 */
function sleep(milliseconds) {
  var start = new Date().getTime()
  for (var i = 0; i < 1e7; i++) {
    if (new Date().getTime() - start > milliseconds) {
      break
    }
  }
}

/**
 *   @author Marcello Costa
 *
 *   Function to delete a browser cookie (does not work with session cookies)
 *
 *   @param  {String}  cookiename    Cookie name
 *
 *   @returns  {Void}
 */
function deleteCookie(cookiename) {
  document.cookie = cookiename + "=; expires=Thu, 01 Jan 1970 00:00:01 GMT;"
}

/**
 *   @author Marcello Costa
 *
 *   Create / update cookie data with pure javascript
 *
 *   @param  {String}  name    Cookie name
 *   @param  {String}  value   Cookie value
 *   @param  {Int}     days    Cookie validity (in days)
 *
 *   @returns  {Void}
 */
function updateDataCookieJS(name, value, days) {
  var expires
  if (days) {
    var date = new Date()
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000)
    expires = "; expires=" + date.toGMTString()
  } else {
    expires = ""
  }
  document.cookie = name + "=" + value + expires + "; path=/"
}

/**
 *   @author Marcello Costa
 *
 *   Retrieves data from a cookie with pure javascript
 *
 *   @param  {String}  cookieName    Cookie name
 *
 *   @returns  {Void}
 */
function getDataCookieJS(cookieName) {
  if (document.cookie.length > 0) {
    cookieStartIndex = document.cookie.indexOf(cookieName + "=")
    if (cookieStartIndex != -1) {
      cookieStartIndex = cookieStartIndex + cookieName.length + 1
      cookieEndIndex = document.cookie.indexOf(";", cookieStartIndex)
      if (cookieEndIndex == -1) {
        cookieEndIndex = document.cookie.length
      }
      return unescape(
        document.cookie.substring(cookieStartIndex, cookieEndIndex)
      )
    }
  }
  return ""
}

/**
 *   @author Marcello Costa
 *
 *   Convert data to be sent via URL
 *
 *   @param  {String}  data    Cookie name
 *   @param  {Bool}    json    Convert received data to JSON
 *
 *   @returns  {String}  String converted to json
 */
function convertDataToPost(data, json) {
  if (json === true) {
    newdatatmp = JSON.stringify(data)
  } else {
    newdatatmp = data
  }

  // Replacing forward slashes "/" with "\ /"
  newdata = replaceAll("/", "\\/", newdatatmp)

  // Returning treated data
  return newdata
}

/**
 *   @author Marcello Costa
 *
 *   Returns the GET parameters of the current request
 *
 *   @returns  {Array}  Associative array with GET keys and values
 */
function getGetParams() {
  urlgets = window.location.search.replace("?", "").split("&")
  params = {}

  for (var i = 0, len = urlgets.length; i < len; i++) {
    if (urlgets[i] !== "") {
      keyvalue = urlgets[i].split("=")
      truekey = keyvalue[0]
      truevalue = keyvalue[1]
      params[truekey] = truevalue
    }
  }

  return params
}

/**
 *   @author Marcello Costa
 *
 *   Converts a timestamp to PT-BR format
 *
 *   @param  {String}  timestamp    Timestamp
 *
 *   @returns  {Void}
 */
function convertTimeStamp(timestamp) {
  timestamp_tmp = timestamp.split("-")

  ano = timestamp_tmp[0]
  mes = timestamp_tmp[1]
  diahora = timestamp_tmp[2]

  dia_tmp = diahora.split(" ")
  dia = dia_tmp[0]
  hora = dia_tmp[1]

  return dia + "/" + mes + "/" + ano + " " + hora
}

/**
 *   @author Marcello Costa
 *
 *   Compare two strings that are dates.
 *   If the start date is greater than the end date, it returns false.
 *
 *   @param  {String}  dataInicial    Initial date
 *   @param  {String}  dataFinal      Final date
 *   @param  {String}  format         Date format
 *
 *   @returns  {Bool}
 */
function firstDateIsGreater(dataInicial, dataFinal, format = "YYYY-MM-DD") {
  data_1 = moment(dataInicial, format)
  data_2 = moment(dataFinal, format)

  if (data_1 == "Invalid Date") {
    throw new Error("Invalid Date Format")
  }

  if (data_1 === data_2) {
    return true
  }
  if (data_1 < data_2) {
    return true
  } else {
    return false
  }
}

/* Strip html tags (first method) */
/**
 *   @author Martijn
 *   @see {http://stackoverflow.com/questions/5499078/fastest-method-to-escape-html-tags-as-html-entities}
 *
 *   Remove tags html de uma string
 *
 *   @param  {String}  str    String to be processed
 *
 *   @returns  {String}  Processed string
 */
var tagsToReplace = {
  "&": "&amp;",
  "<": "&lt;",
  ">": "&gt;",
}

function replaceTag(tag) {
  return tagsToReplace[tag] || tag
}

function safeTagsReplace(str) {
  return str.replace(/[&<>]/g, replaceTag)
}

/* Strip html tags (according to method) */
/**
 *   @author VyvIT, Robert K
 *   @see {http://stackoverflow.com/questions/5796718/html-entity-decode}
 *
 *   Removes html tags from a string. To use make: decodeEntities('<img src=fake onerror="prompt(1)">');
 *
 *   @param {String} str String to be processed
 *
 *   @returns {String} Processed string
 */
function decodeEntities() {
  var doc = document.implementation.createHTMLDocument("")
  var element = doc.createElement("div")

  function getText(str) {
    element.innerHTML = str
    str = element.textContent
    element.textContent = ""
    return str
  }

  function decodeHTMLEntities(str) {
    if (str && typeof str === "string") {
      var x = getText(str)
      while (str !== x) {
        str = x
        x = getText(x)
      }
      return x
    }
  }
  return decodeHTMLEntities
}
/* End of the html tag strip */
