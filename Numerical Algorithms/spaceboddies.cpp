// To run the parallel version add the PARALLEL definition to the compilation command
//
// Serial Compile command
// g++ -O3 --std=c++11 spaceboddies.c -o spaceboddies
//
// Parallel Compile command
// g++ -O3 --std=c++11 -DPARALLEL -fopenmp spaceboddies.c -o spaceboddies
//

#include <fstream>
#include <sstream>
#include <iostream>
#include <iomanip>
#include <vector>
#include <string>
#include <math.h>
#include <utility>
#include <list>
#include <omp.h>


double t = 0;
double h = 1e-15;
double h_new = 0.0001;
double tFinal = 0;

int NumberOfBodies = 0;
int activeBodies = 0;

typedef std::vector<double> double_vec;
class SpaceBody
{
public:
  double_vec x, v, x_e, v_e, x_h, v_h, force, force_e;
  double mass;
  bool active;
  double error;
  SpaceBody(): x(3), v(3), x_e(3), v_e(3), x_h(3), v_h(3), force(3), force_e(3), active(true) {}

  void fuseBody(SpaceBody& otherBody) {
    // new velocity (preserving momentum)
    v[0] = (mass * v[0] + otherBody.mass * otherBody.v[0]) / (mass + otherBody.mass);
    v[1] = (mass * v[1] + otherBody.mass * otherBody.v[1]) / (mass + otherBody.mass);
    v[2] = (mass * v[2] + otherBody.mass * otherBody.v[2]) / (mass + otherBody.mass);
    //combine mass
    mass += otherBody.mass;
    //remove other body
    otherBody.active = false;
    otherBody.clearSelf();
  }
  void clearSelf() {
    v[0] = 0;
    v[1] = 0;
    v[2] = 0;
    force[0]= 0;
    force[1] = 0;
    force[2] = 0;
  }
};

std::vector<SpaceBody> spaceboddies;

void print_vec(std::string label, double_vec& v) {
  std::cout << label << " (" << v[0] << " " << v[1] << " " << v[2] << ")" << std::endl;
}
void subtract(double_vec& result, double_vec& v1, double_vec& v2) {
  result[0] = v1[0] - v2[0];
  result[1] = v1[1] - v2[1];
  result[2] = v1[2] - v2[2];
}

void add(double_vec& result, double_vec& v1, double_vec& v2) {
  result[0] = v1[0] + v2[0];
  result[1] = v1[1] + v2[1];
  result[2] = v1[2] + v2[2];
}

void scalar_add(double_vec& result, double a, double_vec& v1, double_vec& v2) {
  result[0] = a * v1[0] + v2[0];
  result[1] = a * v1[1] + v2[1];
  result[2] = a * v1[2] + v2[2];
}

void double_scalar_add(double_vec& result, double a, double_vec& v1, double b, double_vec& v2) {
  result[0] = a * v1[0] + b * v2[0];
  result[1] = a * v1[1] + b * v2[1];
  result[2] = a * v1[2] + b * v2[2];
}

void scalar_mult(double_vec& result, double c, double_vec& v) {
  result[0] = v[0] * c;
  result[1] = v[1] * c;
  result[2] = v[2] * c;
}

double d(double_vec& v1, double_vec& v2) {
  return sqrt((v1[0] - v2[0]) * (v1[0] - v2[0]) +
              (v1[1] - v2[1]) * (v1[1] - v2[1]) +
              (v1[2] - v2[2]) * (v1[2] - v2[2])
              );
}

double dot(double_vec& v1, double_vec& v2) {
  double result = 0;
  result += v1[0] * v2[0];
  result += v1[1] * v2[1];
  result += v1[2] * v2[2];
  return result;
}

void setUp(int argc, char** argv) {
  NumberOfBodies = (argc-2) / 7;
  activeBodies = NumberOfBodies;

  spaceboddies.resize(NumberOfBodies);

  int readArgument = 1;

  tFinal = std::stof(argv[readArgument]); readArgument++;

  for (int i=0; i<NumberOfBodies; i++) {

    spaceboddies[i].x[0] = std::stof(argv[readArgument]); readArgument++;
    spaceboddies[i].x[1] = std::stof(argv[readArgument]); readArgument++;
    spaceboddies[i].x[2] = std::stof(argv[readArgument]); readArgument++;

    spaceboddies[i].v[0] = std::stof(argv[readArgument]); readArgument++;
    spaceboddies[i].v[1] = std::stof(argv[readArgument]); readArgument++;
    spaceboddies[i].v[2] = std::stof(argv[readArgument]); readArgument++;

    spaceboddies[i].mass = std::stof(argv[readArgument]); readArgument++;

    if (spaceboddies[i].mass <= 0.0 ) {
      std::cerr << "invalid mass for body " << i << std::endl;
      exit(-2);
    }
  }

  std::cout << "created setup with " << NumberOfBodies << " bodies" << std::endl << std::endl;
}


std::ofstream videoFile;


void openParaviewVideoFile() {
  videoFile.open( "result.pvd" );
  videoFile << "<?xml version=\"1.0\"?>" << std::endl
            << "<VTKFile type=\"Collection\" version=\"0.1\" byte_order=\"LittleEndian\" compressor=\"vtkZLibDataCompressor\">" << std::endl
            << "<Collection>";
}



void closeParaviewVideoFile() {
  videoFile << "</Collection>"
            << "</VTKFile>" << std::endl;
}


/**
 * The file format is documented at http://www.vtk.org/wp-content/uploads/2015/04/file-formats.pdf
 */
void printParaviewSnapshot(int counter) {
  std::stringstream filename;
  filename << "result-" << counter <<  ".vtp";
  std::ofstream out( filename.str().c_str() );
  out << "<VTKFile type=\"PolyData\" >" << std::endl
      << "<PolyData>" << std::endl
      << " <Piece NumberOfPoints=\"" << activeBodies << "\">" << std::endl
      << "  <Points>" << std::endl
      << "   <DataArray type=\"Float32\" NumberOfComponents=\"3\" format=\"ascii\">";

  for (int i=0; i<NumberOfBodies; i++) {
    if(!spaceboddies[i].active) continue;
    out << spaceboddies[i].x[0]
        << " "
        << spaceboddies[i].x[1]
        << " "
        << spaceboddies[i].x[2]
        << " ";
  }

  out << "   </DataArray>" << std::endl
      << "  </Points>" << std::endl
      << " </Piece>" << std::endl
      << "</PolyData>" << std::endl
      << "</VTKFile>"  << std::endl;

  videoFile << "<DataSet timestep=\"" << counter << "\" group=\"\" part=\"0\" file=\"" << filename.str() << "\"/>" << std::endl;
}

#if defined PARALLEL

void updateBody() {
  std::cout << "parallel" << std::endl;
  double timeStepSize = h;

  ///////////////////////////
  // CHECK FOR COLLISIONS //
  /////////////////////////
  typedef std::pair<int,int> BodyPair;
  typedef std::list<BodyPair> FusionList;
  FusionList fusion_candidates = FusionList();

 //add fusion candidates
#pragma omp parallel for 
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;

    for (int i=j+1; i<NumberOfBodies; i++) {
      if (spaceboddies[i].active == false) continue;
      const double distance = sqrt(
                                   (spaceboddies[j].x[0]-spaceboddies[i].x[0]) * (spaceboddies[j].x[0]-spaceboddies[i].x[0]) +
                                   (spaceboddies[j].x[1]-spaceboddies[i].x[1]) * (spaceboddies[j].x[1]-spaceboddies[i].x[1]) +
                                   (spaceboddies[j].x[2]-spaceboddies[i].x[2]) * (spaceboddies[j].x[2]-spaceboddies[i].x[2])
                                   );
      if (distance < 1e-8) {
#pragma omp critical (appendToFusionList)
        fusion_candidates.push_back(BodyPair(j, i));
      }
    }
  }

  // perform fusions serially
  for (FusionList::const_iterator p = fusion_candidates.begin(); p != fusion_candidates.end(); ++p) {
    int j = p->first; int i = p->second;
    //sanity check
    if(spaceboddies[j].active && spaceboddies[i].active) {
        spaceboddies[j].fuseBody(spaceboddies[i]);
        std::cout << "Fusion: " << "bodyA (id=" << j << "); bodyB (id=" << i << ")" << std::endl;
        std::cout << "Time: " << std::setprecision(15) << t  << std::endl;
        std::cout << "TimestepSize: " << timeStepSize << std::endl;
        print_vec("positionA: ", spaceboddies[j].x);
        print_vec("positionB: ", spaceboddies[i].x);
        std::cout << "distance: " << d(spaceboddies[j].x, spaceboddies[i].x)  << std::endl;
        --activeBodies;
    }
  }

  ////////////////////
  // HEUN'S METHOD //
  //////////////////

  // updateForces
#pragma omp parallel for
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;

    spaceboddies[j].force[0] = 0.0;
    spaceboddies[j].force[1] = 0.0;
    spaceboddies[j].force[2] = 0.0;

    for (int i=0; i<NumberOfBodies; i++) {
      if (i == j || spaceboddies[i].active == false) continue;

      const double distance = d(spaceboddies[j].x, spaceboddies[i].x);
      double g = spaceboddies[i].mass * spaceboddies[j].mass /distance /distance /distance;
      // F =  x_i - x_j
      scalar_add(spaceboddies[j].force, -1, spaceboddies[j].x, spaceboddies[i].x);
      // F = F * g
      scalar_mult(spaceboddies[j].force, g, spaceboddies[j].force);
    }
  }

  //position Estimates (Euler)
#pragma omp parallel for
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;
    //x_e = dt * v + x
    scalar_add(spaceboddies[j].x_e, timeStepSize, spaceboddies[j].v, spaceboddies[j].x);
  }

  //velocity Estimates (Euler)
#pragma omp parallel for
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;
    //v_e = dt/m * F + v
    scalar_add(spaceboddies[j].v_e, timeStepSize/spaceboddies[j].mass, spaceboddies[j].force, spaceboddies[j].v);
  }

  // calculate force at Euler's position
#pragma omp parallel for
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;

    spaceboddies[j].force_e[0] = 0.0;
    spaceboddies[j].force_e[1] = 0.0;
    spaceboddies[j].force_e[2] = 0.0;

    for (int i=0; i<NumberOfBodies; i++) {
      if (spaceboddies[j].active == false) continue;
      if (i == j || spaceboddies[i].active == false) continue;

      const double distance = d(spaceboddies[j].x_e, spaceboddies[i].x_e);
      double g = spaceboddies[i].mass * spaceboddies[j].mass /distance /distance /distance;
      // F_e =  xe_i - xe_j
      scalar_add(spaceboddies[j].force_e, -1, spaceboddies[j].x_e, spaceboddies[i].x_e);
      // F_e = F_e * g
      scalar_mult(spaceboddies[j].force_e, g, spaceboddies[j].force_e);
    }
  }

  // Heun's position estimates
#pragma omp parallel for
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;
    //x_h = v_e + v
    add(spaceboddies[j].x_h, spaceboddies[j].v_e, spaceboddies[j].v);
    //x_h = dt*0.5*x_h + h
    scalar_add(spaceboddies[j].x_h, timeStepSize*0.5, spaceboddies[j].x_h, spaceboddies[j].x);
    spaceboddies[j].x = spaceboddies[j].x_h;
  }

  // Heun's velocity estimates
#pragma omp parallel for
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;
    // v_h = F + F_e
    add(spaceboddies[j].v_h, spaceboddies[j].force, spaceboddies[j].force_e);
    // v_h = dt/m * 0.5 * v_h + v
    scalar_add(spaceboddies[j].v_h, timeStepSize/spaceboddies[j].mass*0.5, spaceboddies[j].v_h, spaceboddies[j].v);
    spaceboddies[j].v = spaceboddies[j].v_h;
  }

  ////////////////////////////
  // ADAPTIVE TIMESTEPPING //
  //////////////////////////

  // estimate LTE
  double max_error = 0;
  double error_sum = 0;
#pragma omp parallel for
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;
    double error = d(spaceboddies[j].x_e, spaceboddies[j].x_h) + tFinal * d(spaceboddies[j].v_e, spaceboddies[j].v_h);
#pragma omp atomic
    error_sum += error;
#pragma omp critical (errorReduce)
    max_error = error > max_error ? error : max_error;
  }

  // adjust time step size
  double tolerance = pow(0.9, NumberOfBodies - 1) * NumberOfBodies * max_error;
  double step_change_factor = max_error == 0 ? 2 : sqrt(tolerance/error_sum);
  h_new = h * step_change_factor;
  h_new = h_new < 1e-15 ? 1e-15 : h_new;
  h_new = h_new > 1e-2 ? 1e-2 : h_new;
  timeStepSize = h_new;
  h = h_new;

  t += timeStepSize;
}

#else

void updateBody() {

  std::cout << "serial" << std::endl;
  double timeStepSize = h;

  ///////////////////////////
  // CHECK FOR COLLISIONS //
  /////////////////////////
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;

    for (int i=j+1; i<NumberOfBodies; i++) {
      if (spaceboddies[i].active == false) continue;

      const double distance = d(spaceboddies[j].x, spaceboddies[i].x);
      if (distance <= 1e-8) {
        spaceboddies[j].fuseBody(spaceboddies[i]);
        std::cout << "Fusion: " << "bodyA (id=" << j << "); bodyB (id=" << i << ")" << std::endl;
        std::cout << "Time: " << std::setprecision(15) << t  << std::endl;
        std::cout << "TimestepSize: " << timeStepSize << std::endl;
        print_vec("positionA: ", spaceboddies[j].x);
        print_vec("positionB: ", spaceboddies[i].x);
        std::cout << "distance: " << d(spaceboddies[j].x, spaceboddies[i].x)  << std::endl;
        --activeBodies;
      }
    }
  }

  ////////////////////
  // HEUN'S METHOD //
  //////////////////

  // updateForces
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;

    spaceboddies[j].force[0] = 0.0;
    spaceboddies[j].force[1] = 0.0;
    spaceboddies[j].force[2] = 0.0;

    for (int i=0; i<NumberOfBodies; i++) {
      if (i == j || spaceboddies[i].active == false) continue;

      const double distance = d(spaceboddies[j].x, spaceboddies[i].x);
      double g = spaceboddies[i].mass * spaceboddies[j].mass /distance /distance /distance;
      // F =  x_i - x_j
      scalar_add(spaceboddies[j].force, -1, spaceboddies[j].x, spaceboddies[i].x);
      // F = F * g
      scalar_mult(spaceboddies[j].force, g, spaceboddies[j].force);
    }
  }

  //position Estimates (Euler)
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;
    //x_e = dt * v + x
    scalar_add(spaceboddies[j].x_e, timeStepSize, spaceboddies[j].v, spaceboddies[j].x);
  }

  //velocity Estimates (Euler)
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;
    //v_e = dt/m * F + v
    scalar_add(spaceboddies[j].v_e, timeStepSize/spaceboddies[j].mass, spaceboddies[j].force, spaceboddies[j].v);
  }

  // calculate force at Euler's position
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;

    spaceboddies[j].force_e[0] = 0.0;
    spaceboddies[j].force_e[1] = 0.0;
    spaceboddies[j].force_e[2] = 0.0;

    for (int i=0; i<NumberOfBodies; i++) {
      if (spaceboddies[j].active == false) continue;
      if (i == j || spaceboddies[i].active == false) continue;

      const double distance = d(spaceboddies[j].x_e, spaceboddies[i].x_e);
      double g = spaceboddies[i].mass * spaceboddies[j].mass /distance /distance /distance;
      // F_e =  xe_i - xe_j
      scalar_add(spaceboddies[j].force_e, -1, spaceboddies[j].x_e, spaceboddies[i].x_e);
      // F_e = F_e * g
      scalar_mult(spaceboddies[j].force_e, g, spaceboddies[j].force_e);
    }
  }

  // Heun's position estimates
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;
    //x_h = v_e + v
    add(spaceboddies[j].x_h, spaceboddies[j].v_e, spaceboddies[j].v);
    //x_h = dt*0.5*x_h + h
    scalar_add(spaceboddies[j].x_h, timeStepSize*0.5, spaceboddies[j].x_h, spaceboddies[j].x);
    spaceboddies[j].x = spaceboddies[j].x_h;
  }

  // Heun's velocity estimates
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;
    // v_h = F + F_e
    add(spaceboddies[j].v_h, spaceboddies[j].force, spaceboddies[j].force_e);
    // v_h = dt/m * 0.5 * v_h + v
    scalar_add(spaceboddies[j].v_h, timeStepSize/spaceboddies[j].mass*0.5, spaceboddies[j].v_h, spaceboddies[j].v);
    spaceboddies[j].v = spaceboddies[j].v_h;
  }

  ////////////////////////////
  // ADAPTIVE TIMESTEPPING //
  //////////////////////////

  // estimate LTE
  double max_error = 0;
  double error_sum = 0;
  double error = 0;
  for (int j=0; j<NumberOfBodies; ++j) {
    if (spaceboddies[j].active == false) continue;

    error = d(spaceboddies[j].x_e, spaceboddies[j].x_h) + tFinal * d(spaceboddies[j].v_e, spaceboddies[j].v_h);
    error_sum += error;
    max_error = error > max_error ? error : max_error;
  }

  // adjust time step size
  double tolerance = pow(0.9, NumberOfBodies - 1) * NumberOfBodies * max_error;
  double step_change_factor = max_error == 0 ? 2 : sqrt(tolerance/error_sum);
  h_new = h * step_change_factor;
  h_new = h_new < 1e-15 ? 1e-15 : h_new;
  h_new = h_new > 1e-2 ? 1e-2 : h_new;
  timeStepSize = h_new;
  h = h_new;

  t += timeStepSize;
}

#endif


int main(int argc, char** argv) {
  if (argc==1) {
    std::cerr << "please add the final time plus a list of object configurations as tuples px py pz vx vy vz m" << std::endl
              << std::endl
              << "Examples:" << std::endl
              << "100.0   0 0 0 1.0   0   0 1.0 \t One body moving form the coordinate system's centre along x axis with speed 1" << std::endl
              << "100.0   0 0 0 1.0   0   0 1.0 0 1.0 0 1.0 0   0 1.0 \t One spiralling around the other one" << std::endl
              << "100.0 3.0 0 0   0 1.0   0 0.4 0   0 0   0 0   0 0.2 2.0 0 0 0 0 0 1.0 \t Three body setup from first lecture" << std::endl
              << std::endl
              << "In this naive code, only the first body moves" << std::endl;

    return -1;
  }
  else if ( (argc-2)%7!=0 ) {
    std::cerr << "error in arguments: each planet is given by seven entries (position, velocity, mass)" << std::endl;
    return -2;
  }

  setUp(argc,argv);

  openParaviewVideoFile();

  printParaviewSnapshot(0);

  int numPlots = 100;
  int timesPlotted = 1;
  bool plotMade = false;
  const double plotIntervals = tFinal/numPlots;
  while (t<=tFinal) {
    updateBody();
    if (t - plotIntervals*timesPlotted > 0) {
      if (!plotMade) {
        printParaviewSnapshot(timesPlotted);
        plotMade = true;
        timesPlotted++;
      }
    } else plotMade = false;
  }

  closeParaviewVideoFile();

  return 0;
}
