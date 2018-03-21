// MultiJointModel.js (c) 2012 matsuda and itami
// Vertex shader program
var VSHADER_SOURCE =
  'attribute vec4 a_Position;\n' +
  'attribute vec4 a_Normal;\n' +
  'uniform mat4 u_ModelMatrix;\n' +    // Model matrix
  'uniform mat4 u_MvpMatrix;\n' +
  'uniform mat4 u_NormalMatrix;\n' +
  'uniform vec4 u_Color;\n' +
  'varying vec4 v_Color;\n' +
  'varying vec3 v_Normal;\n' +
  'varying vec3 v_dirNormal;\n' +
  'varying vec3 v_Position;\n' +
  'void main() {\n' +
  '  gl_Position = u_MvpMatrix * a_Position;\n' +
  '  v_Position = vec3(u_ModelMatrix * a_Position);\n' +
  '  v_Normal = normalize(vec3(u_NormalMatrix * a_Normal));\n' +
  '  v_dirNormal = normalize(a_Normal.xyz);\n' +
  '  v_Color = u_Color;\n' + 

  '}\n';

// Fragment shader program
var FSHADER_SOURCE =
  '#ifdef GL_ES\n' +
  'precision mediump float;\n' +
  '#endif\n' +
  'varying vec4 v_Color;\n' +
  'varying vec3 v_Normal;\n' +
  'varying vec3 v_dirNormal;\n' +
  'varying vec3 v_Position;\n' +
  'void main() {\n' +
  // Shading calculation to make the arm look three-dimensional
  '  vec3 lightColor = vec3(1.0, 1.0, 1.0);\n' + // Light direction
  '  vec3 lightPosition = vec3(20.3, 7.0, 20.5);\n' + // Light direction
  '  vec3 ambientLight = vec3(0.05, 0.05, 0.05);\n' + // Light direction
//Directional Lighting
  '  vec4 color = v_Color;\n' +  // Robot color
  '  vec3 dirLightDirection = normalize(vec3(0.0, 5.0, 0.0));\n' + // Light direction
  '  vec3 dirNormal = v_dirNormal;\n' +
  '  float dirnDotL = max(dot(dirLightDirection, dirNormal), 0.0);\n' +
  '  vec3 dirDiffuse = lightColor * color.rgb * dirnDotL;\n' +
//Point Lighting
  '  vec3 normal = v_Normal;\n' +
  '  vec3 lightDirection = normalize(lightPosition - v_Position);\n' +
  '  float nDotL = max(dot(normal, lightDirection), 0.0);\n' +
  '  vec3 diffuse = lightColor * color.rgb * nDotL;\n' +
  '  vec3 ambient = ambientLight * color.rgb;\n' +
//Final Lighting
  '  gl_FragColor = vec4(diffuse + dirDiffuse + ambient, color.a);\n' + 
  '}\n';

function main() {
  // Retrieve <canvas> element
  var canvas = document.getElementById('webgl');

  // Get the rendering context for WebGL
  var gl = getWebGLContext(canvas);
  if (!gl) {
    console.log('Failed to get the rendering context for WebGL');
    return;
  }

  // Initialize shaders
  if (!initShaders(gl, VSHADER_SOURCE, FSHADER_SOURCE)) {
    console.log('Failed to intialize shaders.');
    return;
  }

  // Set the vertex information
  var n = initVertexBuffers(gl);
  if (n < 0) {
    console.log('Failed to set the vertex information');
    return;
  }

  // Set the clear color and enable the depth test
  gl.clearColor(49/255, 213/255, 249/255, 1.0);
  gl.enable(gl.DEPTH_TEST);

  // Get the storage locations of uniform variables
  var u_MvpMatrix = gl.getUniformLocation(gl.program, 'u_MvpMatrix');
  var u_NormalMatrix = gl.getUniformLocation(gl.program, 'u_NormalMatrix');
  var u_Color = gl.getUniformLocation(gl.program, 'u_Color');
  var u_ModelMatrix = gl.getUniformLocation(gl.program, 'u_ModelMatrix');
  console.log(u_ModelMatrix);
  if (!u_MvpMatrix || !u_NormalMatrix || !u_Color) {
    console.log('Failed to get the storage location');
    if(!u_MvpMatrix)
	console.log("MVP");
    else if(!u_NormalMatrix)
	console.log("NORMAL")
    else if (!u_Color) 
	console.log("COLOR")
    else if (!u_ModelMatrix) 
	console.log("Model")
    return;
  }

  // Calculate the view projection matrix
  var viewProjMatrix = new Matrix4();
  viewProjMatrix.setPerspective(50.0, canvas.width / canvas.height, 1.0, 250.0);
  viewProjMatrix.lookAt(20.0, 60.0, 100.0, 0.0, 0.0, 0.0, 0.0, 1.0, 0.0);

  var tick = function() {
    //updateScene: defined in movements.js
    updateScene();//updates model transformation params based on key presses
    draw(gl, n, viewProjMatrix, u_MvpMatrix, u_NormalMatrix, u_Color, u_ModelMatrix);
    requestAnimationFrame(tick, canvas); // Request that the browser calls tick
  };
  tick();
}


function initVertexBuffers(gl) {
  // Coordinates（Cube which length of one side is 1 with the origin on the center of the bottom)
  var vertices = new Float32Array([
    0.5, 1.0, 0.5,  0.5, 1.0,-0.5, -0.5, 1.0,-0.5, -0.5, 1.0, 0.5, // v0-v5-v6-v1 up
    0.5, 1.0, 0.5, -0.5, 1.0, 0.5, -0.5, 0.0, 0.5,  0.5, 0.0, 0.5, // v0-v1-v2-v3 front
    0.5, 1.0, 0.5,  0.5, 0.0, 0.5,  0.5, 0.0,-0.5,  0.5, 1.0,-0.5, // v0-v3-v4-v5 right
   -0.5, 1.0, 0.5, -0.5, 1.0,-0.5, -0.5, 0.0,-0.5, -0.5, 0.0, 0.5, // v1-v6-v7-v2 left
   -0.5, 0.0,-0.5,  0.5, 0.0,-0.5,  0.5, 0.0, 0.5, -0.5, 0.0, 0.5, // v7-v4-v3-v2 down
    0.5, 0.0,-0.5, -0.5, 0.0,-0.5, -0.5, 1.0,-0.5,  0.5, 1.0,-0.5  // v4-v7-v6-v5 back
  ]);

  // Normal
  var normals = new Float32Array([
    0.0, 1.0, 0.0,  0.0, 1.0, 0.0,  0.0, 1.0, 0.0,  0.0, 1.0, 0.0, // v0-v5-v6-v1 up
    0.0, 0.0, 1.0,  0.0, 0.0, 1.0,  0.0, 0.0, 1.0,  0.0, 0.0, 1.0, // v0-v1-v2-v3 front
    1.0, 0.0, 0.0,  1.0, 0.0, 0.0,  1.0, 0.0, 0.0,  1.0, 0.0, 0.0, // v0-v3-v4-v5 right
   -1.0, 0.0, 0.0, -1.0, 0.0, 0.0, -1.0, 0.0, 0.0, -1.0, 0.0, 0.0, // v1-v6-v7-v2 left
    0.0,-1.0, 0.0,  0.0,-1.0, 0.0,  0.0,-1.0, 0.0,  0.0,-1.0, 0.0, // v7-v4-v3-v2 down
    0.0, 0.0,-1.0,  0.0, 0.0,-1.0,  0.0, 0.0,-1.0,  0.0, 0.0,-1.0  // v4-v7-v6-v5 back
  ]);

  // Indices of the vertices
  var indices = new Uint8Array([
     0, 1, 2,   0, 2, 3,    // front
     4, 5, 6,   4, 6, 7,    // right
     8, 9,10,   8,10,11,    // up
    12,13,14,  12,14,15,    // left
    16,17,18,  16,18,19,    // down
    20,21,22,  20,22,23     // back
  ]);

  // Write the vertex property to buffers (coordinates and normals)
  if (!initArrayBuffer(gl, 'a_Position', vertices, gl.FLOAT, 3)) return -1;
  if (!initArrayBuffer(gl, 'a_Normal', normals, gl.FLOAT, 3)) return -1;

  // Unbind the buffer object
  gl.bindBuffer(gl.ARRAY_BUFFER, null);

  // Write the indices to the buffer object
  var indexBuffer = gl.createBuffer();
  if (!indexBuffer) {
    console.log('Failed to create the buffer object');
    return -1;
  }
  gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, indexBuffer);
  gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, indices, gl.STATIC_DRAW);

  return indices.length;
}

function initArrayBuffer(gl, attribute, data, type, num) {
  // Create a buffer object
  var buffer = gl.createBuffer();
  if (!buffer) {
    console.log('Failed to create the buffer object');
    return false;
  }
  // Write date into the buffer object
  gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
  gl.bufferData(gl.ARRAY_BUFFER, data, gl.STATIC_DRAW);

  // Assign the buffer object to the attribute variable
  var a_attribute = gl.getAttribLocation(gl.program, attribute);
  if (a_attribute < 0) {
    console.log('Failed to get the storage location of ' + attribute);
    return false;
  }
  gl.vertexAttribPointer(a_attribute, num, type, false, 0, 0);
  // Enable the assignment of the buffer object to the attribute variable
  gl.enableVertexAttribArray(a_attribute);

  return true;
}

// Coordinate transformation matrix
var g_modelMatrix = new Matrix4(), g_mvpMatrix = new Matrix4();

function draw(gl, n, viewProjMatrix, u_MvpMatrix, u_NormalMatrix, u_Color, u_ModelMatrix) {
  // Clear color and depth buffer
  gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

  g_modelMatrix.setTranslate(0.0, 0.0, 0.0);
  gl.uniform4fv(u_Color, [62/255, 119/255, 17/255, 1]); //set Box color
  pushMatrix(g_modelMatrix);
      g_modelMatrix.translate(0.0, -10, 0.0);
      drawPlane(gl, n, 200, 10, 200, viewProjMatrix, u_MvpMatrix, u_NormalMatrix, u_ModelMatrix);
  g_modelMatrix = popMatrix();

  // Draw body(lower)
  gl.uniform4fv(u_Color, [1, 0, 0, 1]); //set Box color
  var lbHeight = 5.0;
  var lbWidth = 6.0;
  var lbDepth = 16.0;
  var yMove = Math.sqrt(WHEEL_RADIUS*WHEEL_RADIUS*2);
  g_modelMatrix.setTranslate(0.0, yMove, 0.0);
  g_modelMatrix.translate(position.x, 0.0, position.z);
  g_modelMatrix.rotate(g_carSpin, 0.0, 1.0, 0.0);  // Rotate around the y-axis
  g_modelMatrix.rotate(g_carFlip, 1.0, 0.0, 0.0);  // Rotate around the x-axis
  drawBox(gl, n, lbWidth, lbHeight, lbDepth, viewProjMatrix, u_MvpMatrix, u_NormalMatrix, u_ModelMatrix);

  // Draw body(upper)
  gl.uniform4fv(u_Color, [4/255, 2/255, 43/255, 1]); //set Box color
  var ubHeight = 3.0;
  var ubWidth = 4.0;
  var ubDepth = 6.0;
  var zMove = (lbDepth - ubDepth)/4
  g_modelMatrix.translate(0, lbHeight, 0.0);
  pushMatrix(g_modelMatrix);
      g_modelMatrix.translate(0, 0, -zMove);
      drawBox(gl, n, ubWidth, ubHeight, ubDepth, viewProjMatrix, u_MvpMatrix, u_NormalMatrix, u_ModelMatrix);
  g_modelMatrix = popMatrix();

  var doorHeight = 4.0;
  var doorWidth = 1.0;
  var doorDepth = 3.0;
  var xMove = doorWidth + 0.5*(lbWidth - doorWidth);
  var xMove = lbWidth/2;
  var yMove = doorHeight + 0.5*(lbHeight - doorHeight);
  var yMove = lbHeight/2;
  var zMove = lbDepth/16;// offset from centre of car body in z direction

  gl.uniform4fv(u_Color, [191/255, 13/255, 60/255, 1]); //set Box color
  // Draw left door
  pushMatrix(g_modelMatrix);
      g_modelMatrix.translate(-xMove, -yMove, zMove);
      g_modelMatrix.rotate(-90, 1.0, 0.0, 0.0);  // Rotate around the x-axis
      g_modelMatrix.rotate(g_doorSwing, 0.0, 0.0, 1.0);  // Rotate around the y-axis
      drawBox(gl, n, doorWidth, doorHeight, doorDepth, viewProjMatrix, u_MvpMatrix, u_NormalMatrix, u_ModelMatrix);
  g_modelMatrix = popMatrix();

  // Draw right door
  pushMatrix(g_modelMatrix);
      g_modelMatrix.translate(xMove, -yMove, zMove);
      g_modelMatrix.rotate(-90, 1.0, 0.0, 0.0);  // Rotate around the x-axis
      g_modelMatrix.rotate(-g_doorSwing, 0.0, 0.0, 1.0);  // Rotate around the y-axis
      drawBox(gl, n, doorWidth, doorHeight, doorDepth, viewProjMatrix, u_MvpMatrix, u_NormalMatrix, u_ModelMatrix);
  g_modelMatrix = popMatrix();

  //g_modelMatrix.translate(0, -lbHeight, 0.0);//move back to base (bottom-middle lb)

  var wheelHeight = 3.0;
  var wheelWidth = 1.0;
  var wheelDepth = 3.0;
  var xMove = lbWidth/2;
  var yMove = lbHeight + 0.5*wheelHeight;
  var zMove = lbDepth*2/6;
  var rotateAngle = g_wheelRotate;
  var rads = Math.PI*rotateAngle/180;
  var yAdjustment = WHEEL_RADIUS*(1-Math.cos(rads));
  var zAdjustment = WHEEL_RADIUS*(1-Math.sin(rads)) - 1;

  gl.uniform4fv(u_Color, [0, 0, 0, 1]); //set Box color
  // Draw wheel front-left
  pushMatrix(g_modelMatrix);
      g_modelMatrix.translate(-xMove, -yMove, zMove);
      g_modelMatrix.translate(0, yAdjustment, zAdjustment); //extra translation to re-centre wheel
      g_modelMatrix.rotate(rotateAngle, 1.0, 0.0, 0.0)  // Rotate around the x-axis
      drawBox(gl, n, wheelWidth, wheelHeight, wheelDepth, viewProjMatrix, u_MvpMatrix, u_NormalMatrix, u_ModelMatrix);
  g_modelMatrix = popMatrix();
  // Draw wheel front-right
  pushMatrix(g_modelMatrix);
      g_modelMatrix.translate(xMove, -yMove, zMove);
      g_modelMatrix.translate(0, yAdjustment, zAdjustment); //extra translation to re-centre wheel
      g_modelMatrix.rotate(rotateAngle, 1.0, 0.0, 0.0)  // Rotate around the x-axis
      drawBox(gl, n, wheelWidth, wheelHeight, wheelDepth, viewProjMatrix, u_MvpMatrix, u_NormalMatrix, u_ModelMatrix);
  g_modelMatrix = popMatrix();
  // Draw wheel back-left
  pushMatrix(g_modelMatrix);
      g_modelMatrix.translate(-xMove, -yMove, -zMove);
      g_modelMatrix.translate(0, yAdjustment, zAdjustment); //extra translation to re-centre wheel
      g_modelMatrix.rotate(rotateAngle, 1.0, 0.0, 0.0)  // Rotate around the x-axis
      drawBox(gl, n, wheelWidth, wheelHeight, wheelDepth, viewProjMatrix, u_MvpMatrix, u_NormalMatrix, u_ModelMatrix);
  g_modelMatrix = popMatrix();
  // Draw wheel back-right
  pushMatrix(g_modelMatrix);
      g_modelMatrix.translate(xMove, -yMove, -zMove);
      g_modelMatrix.translate(0, yAdjustment, zAdjustment); //extra translation to re-centre wheel
      g_modelMatrix.rotate(rotateAngle, 1.0, 0.0, 0.0)  // Rotate around the x-axis
      drawBox(gl, n, wheelWidth, wheelHeight, wheelDepth, viewProjMatrix, u_MvpMatrix, u_NormalMatrix, u_ModelMatrix);
  g_modelMatrix = popMatrix();
}

var g_matrixStack = []; // Array for storing a matrix
function pushMatrix(m) { // Store the specified matrix to the array
  var m2 = new Matrix4(m);
  g_matrixStack.push(m2);
}

function popMatrix() { // Retrieve the matrix from the array
  return g_matrixStack.pop();
}

var g_normalMatrix = new Matrix4();  // Coordinate transformation matrix for normals

// Draw rectangular solid
function drawBox(gl, n, width, height, depth, viewProjMatrix, u_MvpMatrix, u_NormalMatrix, u_ModelMatrix) {
  pushMatrix(g_modelMatrix);   // Save the model matrix
    // Scale a cube and draw
    g_modelMatrix.scale(width, height, depth);
    gl.uniformMatrix4fv(u_MvpMatrix, false, g_modelMatrix.elements);
    // Calculate the model view project matrix and pass it to u_MvpMatrix
    g_mvpMatrix.set(viewProjMatrix);
    g_mvpMatrix.multiply(g_modelMatrix);
    gl.uniformMatrix4fv(u_MvpMatrix, false, g_mvpMatrix.elements);
    // Calculate the normal transformation matrix and pass it to u_NormalMatrix
    g_normalMatrix.setInverseOf(g_modelMatrix);
    g_normalMatrix.transpose();
    gl.uniformMatrix4fv(u_NormalMatrix, false, g_normalMatrix.elements);
    // Draw
    gl.drawElements(gl.TRIANGLES, n, gl.UNSIGNED_BYTE, 0);
  g_modelMatrix = popMatrix();   // Retrieve the model matrix
}

// Draw rectangular solid
function drawPlane(gl, n, width, height, depth, viewProjMatrix, u_MvpMatrix, u_NormalMatrix, u_ModelMatrix) {
  pushMatrix(g_modelMatrix);   // Save the model matrix
    // Scale a cube and draw
    g_modelMatrix.scale(width, height, depth);
    gl.uniformMatrix4fv(u_MvpMatrix, false, g_modelMatrix.elements);
    // Calculate the model view project matrix and pass it to u_MvpMatrix
    g_mvpMatrix.set(viewProjMatrix);
    g_mvpMatrix.multiply(g_modelMatrix);
    gl.uniformMatrix4fv(u_MvpMatrix, false, g_mvpMatrix.elements);
    // Calculate the normal transformation matrix and pass it to u_NormalMatrix
    g_normalMatrix.setInverseOf(g_modelMatrix);
    g_normalMatrix.transpose();
    gl.uniformMatrix4fv(u_NormalMatrix, false, g_normalMatrix.elements);
    // Draw
    gl.drawElements(gl.TRIANGLES, 6, gl.UNSIGNED_BYTE, 0);
  g_modelMatrix = popMatrix();   // Retrieve the model matrix
}
