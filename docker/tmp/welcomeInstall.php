<html>
  <head>
    <style>
      * {
        font-family: Verdana;
        color: #333;
      }

      body {
        padding: 30px;
      }

      #progressContainer {
        display: flex;
        align-items: center;
        justify-content: center;
      }

      #progressBar {
        width: 15%;
        border: 1px solid #a0c2a8;
      }

      #background {
        background-color: #2c89a0;
        border: 1px solid #ccc;
        height: 20px;
        width: 50%;
        margin-left: 0;
      }
    </style>
  </head>
  <body>
    <div style="text-align: center; margin-bottom: 20px;">
      <img src="favicon.png" /><br />
      <h2>Installing system...</h2>
      <br />
      <div style="font-size: 14px;">
        Please wait until all dependencies are downloaded
      </div>
    </div>

    <div id="progressContainer">
      <div id="progressBar">
        <div id="background"></div>
      </div>
    </div>
  </body>

  <script>
    function progressAnimation() {
      var background = document.getElementById("background")
      var width = 1
      var id = setInterval(move, 50)

      var currentBarWidth = 0
      var direction = 0
      function move() {
        if (direction === 0) {
          currentBarWidth++
          background.style.margin = "0 0 0 " + currentBarWidth + "%"

          if (currentBarWidth >= 49) {
            direction = 1
          }
        } else {
          currentBarWidth--
          background.style.margin = "0 0 0 " + currentBarWidth + "%"

          if (currentBarWidth === 0) {
            direction = 0
          }
        }

        if (currentBarWidth === 0){
          window.location.reload(true); 
        }
      }
    }
    progressAnimation();
  </script>
</html>
