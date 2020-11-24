
<!DOCTYPE html>
<html>
<head>


<script>
//modified from http://jsfiddle.net/bencentra/q1s8gmqv/?utm_source=website&utm_medium=embed&utm_campaign=q1s8gmqv
var canvas;
var context;
var loop;
var leftPaddle;
var rightPaddle;
var ball;
var paddleWidth = 25;
var paddleHeight = 100;
var ballSize = 10;
var ballSpeed = 2;
var paddleSpeed = 2;
var drawables = [];
// Key Codes
var W = 87;
var S = 83;
var UP = 38;
var DOWN = 40;

var flipped = false;
var spinning = false;
var randomPaddles = false;
var highSpeed = false;

var color1 = '#BC0000';
var color2 = '#0000BC';

var time;
var rotation = 0;


var sentScore = false;

// Keep track of pressed keys
var keys = {
    W: false,
    S: false,
    UP: false,
    DOWN: false
};


// Keep track of the score
var leftScore = 0;
var rightScore = 0;
function init() {
    canvas = document.getElementById("board");
    if (canvas.getContext) {
        context = canvas.getContext("2d");
        leftPaddle = makeRect(25, canvas.height / 2 - paddleHeight / 2, paddleWidth, paddleHeight, 5, color1);
        rightPaddle = makeRect(canvas.width - paddleWidth - 25, canvas.height / 2 - paddleHeight / 2, paddleWidth, paddleHeight, 5, color2);
        ball = makeRect(0, 0, ballSize, ballSize, ballSpeed, '#000000');
        drawables.push(leftPaddle);
        drawables.push(rightPaddle);
        drawables.push(ball);
        console.log(drawables);
        resetBall();
        attachKeyListeners();
        loop = window.setInterval(gameLoop, 16); //16ms
        canvas.focus();
	time = 0;
    }
}
function resetBall() {
    ball.x = canvas.width / 2 - ball.w / 2;
    ball.y = canvas.height / 2 - ball.w / 2;
    // Modify the ball object to have two speed properties, one for X and one for Y
    ball.sX = ballSpeed;
    ball.sY = ballSpeed / 2;

    // Randomize initial direction
    if (Math.random() > 0.5) {
        ball.sX *= -1;
    }
    // Randomize initial direction
    if (Math.random() > 0.5) {
        ball.sY *= -1;
    }
    if(highSpeed) {
	ball.sX *= 3;
	ball.sY *= 3;
    }
}
// Bounce the ball off of a paddle
function bounceBall() {
    // Increase and reverse the X speed
    if (ball.sX > 0) {
        ball.sX += 1;
        // Add some "spin"
        if (keys.UP) {
            ball.sY -= 1;
        } else if (keys.DOWN) {
            ball.sY += 1;
        }
    } else {
        ball.sX -= 1;
        // Add some "spin"
        if (keys.W) {
            ball.sY -= 1;
        } else if (keys.S) {
            ball.sY += 1
        }
    }
    ball.sX *= -1;
}
function attachKeyListeners() {
    // Listen for keydown events
    window.addEventListener('keydown', function (e) {
        console.log("keydown", e);
        if (e.keyCode === W) {
            keys.W = true;
        }
        if (e.keyCode === S) {
            keys.S = true;
        }
        if (e.keyCode === UP) {
            keys.UP = true;
        }
        if (e.keyCode === DOWN) {
            keys.DOWN = true;
        }
        console.log(keys);
    });
    window.addEventListener('keyup', function (e) {
        console.log("keyup", e);
        if (e.keyCode === W) {
            keys.W = false;
        }
        if (e.keyCode === S) {
            keys.S = false;
        }
        if (e.keyCode === UP) {
            keys.UP = false;
        }
        if (e.keyCode === DOWN) {
            keys.DOWN = false;
        }
        console.log(keys);
    });
}
// Create a rectangle object - for paddles, ball, etc
function makeRect(x, y, width, height, speed, color) {
    if (!color)
        color = '#000000';
    return {
        x: x,
        y: y,
        w: width,
        h: height,
        s: speed,
        c: color,
        draw: function () {
            context.fillStyle = this.c;
            context.fillRect(this.x, this.y, this.w, this.h);
        }
    };
}
function doAI() {
    if (ball.x >= canvas.width * .6) {
        let paddleHalf = paddleHeight / 2;
        if (ball.y > rightPaddle.y + paddleHalf) {
            rightPaddle.y += rightPaddle.s;
        } else if (ball.y < rightPaddle.y) {
            rightPaddle.y -= rightPaddle.s;
        }
    }
    clampToCanvas(rightPaddle);
}
function movePaddle() {
    if ((keys.W && !flipped) || (keys.S && flipped)) {
        leftPaddle.y -= leftPaddle.s;
    }
    if ((keys.S && !flipped) || (keys.W && flipped)) {
        leftPaddle.y += leftPaddle.s;
    }
    if ((keys.UP && !flipped) || (keys.DOWN && flipped)) {
        leftPaddle.y -= leftPaddle.s;
    }
    if ((keys.DOWN && !flipped) || (keys.UP && flipped)) {
        leftPaddle.y += leftPaddle.s;
    }
    clampToCanvas(leftPaddle);
}
function clampToCanvas(paddle) {
    if (paddle.y < 0) {
        paddle.y = 0;
    }
    if (paddle.y + paddle.h > canvas.height) {
        paddle.y = canvas.height - paddle.h;
    }
}
function moveBall() {
    // Move the ball
    ball.x += ball.sX;
    ball.y += ball.sY;
    // Bounce the ball off the top/bottom
    if (ball.y < 0 || ball.y + ball.h > canvas.height) {
        ball.sY *= -1;
    }
}
function checkPaddleCollision() {
    // Bounce the ball off the paddles
    if (ball.y + ball.h / 2 >= leftPaddle.y && ball.y + ball.h / 2 <= leftPaddle.y + leftPaddle.h) {
        if (ball.x <= leftPaddle.x + leftPaddle.w) {
            bounceBall();
        }
    }
    if (ball.y + ball.h / 2 >= rightPaddle.y && ball.y + ball.h / 2 <= rightPaddle.y + rightPaddle.h) {
        if (ball.x + ball.w >= rightPaddle.x) {
            bounceBall();
        }
    }
}
function checkScore() {
    // Score if the ball goes past a paddle
    if (ball.x < leftPaddle.x) {
        rightScore++;
        resetBall();
        ball.sX *= -1;
    } else if (ball.x + ball.w > rightPaddle.x + rightPaddle.w) {
        leftScore++;
        resetBall();
        ball.sX *= -1;
    }
}
function drawScores() {
    // Draw the scores
    context.fillStyle = '#000000';
    context.font = '24px Arial';
    context.textAlign = 'left';
    context.fillText('Score: ' + leftScore, 5, 24);
    context.textAlign = 'right';
    context.fillText('Score: ' + rightScore, canvas.width - 5, 24);
}
function drawModifiers() {
    // Draws what modifiers are currently happening
    context.fillStyle = '#000000';
    context.font = '24px Arial';
    context.textAlign = 'center';
    if(flipped)
    {
	context.fillText('Flipped Controls!', canvas.width / 2, 24);
    }
    if(spinning)
    {
	context.fillText('Screen Spin!', canvas.width / 2, 24);
    }
    if(randomPaddles)
    {
	context.fillText('Glitchy Paddles!', canvas.width / 2, 24);
    }
    if(highSpeed)
    {
	context.fillText('High Speed!', canvas.width / 2, 24);
    }
}

function erase() {
    context.fillStyle = '#FFFFFF';
    context.fillRect(0, 0, canvas.width, canvas.height);
}


function spinScreen() {
    if(time % 5 == 0)
    {
	rotation+= 1;
	canvas.style.transform = "rotate(" + rotation + "deg)"
    }
}

function randomPaddleColors() {
    if(time % 120 == 0) {
	leftPaddle.y = Math.floor(Math.random() * canvas.height);
	rightPaddle.y = Math.floor(Math.random() * canvas.height);
    }
}

function checkForEvent() {

if(time >= 600)
{
    if (!flipped && !spinning && !randomPaddles && !highSpeed) {
	var rnd = Math.floor(Math.random() * 4);
	//rnd = 3;    //for testing
	if(rnd == 0)
	{
	    flipped = true;
	}
	if(rnd == 1)
	{
	    spinning = true;
	}
	if(rnd == 2)
	{
	    randomPaddles = true;
	}
	if(rnd == 3)
	{
	    highSpeed = true;
	    ball.sX *= 2;
	    ball.sY *= 2;
	}
    } 
    else {
    	flipped = false;
   	spinning = false;
    	randomPaddles = false;
    	highSpeed = false;
    }
    time = 0;
}
time++;

    if(spinning) {
	spinScreen();
    }
    else {
	canvas.style.transform = "rotate(" + 0 + "deg)";
	rotation = 0;
    }
    if(randomPaddles) {
	randomPaddleColors();
    }
}

function getRandomColor() {
  var letters = '0123456789ABCDEF';
  var color = '#';
  for (var i = 0; i < 6; i++) {
    color += letters[Math.floor(Math.random() * 16)];
  }
  return color;
}

        function sendScore() {

            let xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    let json = JSON.parse(this.responseText);
                    if (json) {
                        if (json.status == 200) {
                            alert("You have gotten some score");
                            location.reload();
                        } else {
                            alert(json.error);
                        }
                    }
                }
            };
	    xhttp.open("POST", "https://web.njit.edu/~ek256/IT202Repo/project/api/changeScore.php", true);
           
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
          
            xhttp.send();	   
        }


function gameLoop() {
    erase();
    movePaddle();
    doAI();
    moveBall();

    checkForEvent();
    checkPaddleCollision();
    checkScore();
    drawScores();
    drawModifiers();
    //draw stuff
    for (let i = 0; i < drawables.length; i++) {
        drawables[i].draw();
    }

    if(leftScore >= 1 && !sentScore)
    {
	sendScore();
	sentScore = true;
    }


}
</script>
</head>
<body onload="init();">
	<a href="http://bencentra.com/2017-07-11-basic-html5-canvas-games.html">Collection of Canvas based games by Ben Centra</a>
	<main>
		<canvas id="board" width="600px" height="600px" style="border: 1px solid black;">
		
		</canvas>
	</main>
</body>
</html>

