var turnMap = {}; // You could also use an array
var moveMap = {}; // You could also use an array
var keyMap = {}
onkeydown = onkeyup = function(e){
  e = e || event; // to deal with IE
  isW = e.keyCode == 87;
  isS = e.keyCode == 83;
  isA = e.keyCode == 65;
  isD = e.keyCode == 68;
  if(isW || isS)
    moveMap[e.keyCode] = e.type == 'keydown';
  else if(isA || isD)
    turnMap[e.keyCode] = e.type == 'keydown';
  else
    keyMap[e.keyCode] = e.type == 'keydown';
}

var ANGLE_STEP = 7.0;     // The increments of rotation angle (degrees)
var g_carSpin = 0.0;   // The rotation angle of arm1 (degrees)
var g_carFlip = 0.0; // The rotation angle of joint1 (degrees)
var g_carMove = 1.0; // The rotation angle of joint1 (degrees)
var g_doorSwing = 0;
var g_wheelRotate = 0.0;  // The rotation angle of joint3 (degrees)
var WHEEL_RADIUS = 1.5;  // The rotation angle of joint3 (degrees)
var BOUNDARY_RADIUS = 90;
//var WHEEL_ROTATE_STEP = 360*g_carMove/(2*Math.PI*wheelRadius) 
var heading = {};
var position = {};
heading.x = 0;
heading.z = 1;
position.x = 0;
position.z = 0;
var speed = 5;

function updateScene(){
  var actionMap = {
    87:/*w*/ function(){
      console.log("moving forward");
      position.x = position.x + heading.x * speed;
      position.z = position.z + heading.z * speed;
      if(Math.abs(position.x) > BOUNDARY_RADIUS)
	  position.x = position.x - heading.x * speed;
      if(Math.abs(position.z) > BOUNDARY_RADIUS)
	  position.z = position.z - heading.z * speed;
      g_wheelRotate += ANGLE_STEP % 360;
    },

    83:/*s*/ function(){
      console.log("moving backward");
      position.x = position.x - heading.x * speed;
      position.z = position.z - heading.z * speed;
      if(Math.abs(position.x) > BOUNDARY_RADIUS)
	  position.x = position.x + heading.x * speed;
      if(Math.abs(position.z) > BOUNDARY_RADIUS)
	  position.z = position.z + heading.z * speed;
      g_wheelRotate -= ANGLE_STEP % 360;
    },

    65:/*a*/ function(){
      console.log("turning left");
      g_carSpin = (g_carSpin + ANGLE_STEP) % 360;
      rad = Math.PI*g_carSpin/180;
      heading.x = Math.sin(rad);
      heading.z = Math.cos(rad);
    },

    68:/*d*/ function() {
      console.log("turning right");
      g_carSpin = (g_carSpin - ANGLE_STEP) % 360;
      rad = Math.PI*g_carSpin/180;
      heading.x = Math.sin(rad);
      heading.z = Math.cos(rad);
    },

    81:/*q -> open doors*/ function() {
      if (g_doorSwing < 60.0)  g_doorSwing = (g_doorSwing + ANGLE_STEP) % 360;
    },

    69:/*e -> close doors*/ function() {
      if (g_doorSwing > 0.0)  g_doorSwing = (g_doorSwing - ANGLE_STEP) % 360;
    },

    39:/*right -> close doors*/ function() {
      g_carSpin = (g_carSpin + ANGLE_STEP) % 360;
    },  
    37:/*left -> close doors*/ function() {
      g_carSpin = (g_carSpin - ANGLE_STEP) % 360;
    },
    40:/*up -> close doors*/ function() {
      g_carFlip = (g_carFlip + ANGLE_STEP) % 360;
    },
    38:/*down -> close doors*/ function() {
      g_carFlip = (g_carFlip - ANGLE_STEP) % 360;
    }  
  };

  var canTurn = false;
  for(keyCode in moveMap) {
    if(moveMap[keyCode] && keyCode in actionMap) {
      actionMap[keyCode]();
      canTurn = true;
    } }
  for(keyCode in turnMap) {
    if(turnMap[keyCode] && canTurn && keyCode in actionMap){
      actionMap[keyCode]();
    } }
  for(keyCode in keyMap) {
    if(keyMap[keyCode] && keyCode in actionMap){
      actionMap[keyCode]();
    } }


}
