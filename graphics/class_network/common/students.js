class Student {
  constructor(name, avgPerModule, accesses, avgTimeSpent) {
    this.name = name;
    this.avgPerModule = avgPerModule;
    this.accesses = accesses;
    this.avgTimeSpent = avgTimeSpent;
  }

  avgTimeForModule(module) {
    return this.avgPerModule[module] || 0;
  }

  get modules() {
    return Object.keys(this.avgPerModule);
  }
}

const allStudents = Object.keys(info).map((studentName) => {
  return new Student(
    studentName,
    info[studentName][0],
    info[studentName][1],
    info[studentName][2]
  );
});
