function secondsToHms(seconds) {
  seconds = Number(seconds);
  const { h, m, s } = extractHmsFromSeconds(seconds);

  function format(x) {
    return ('0' + x).slice(-2);
  }

  if (h <= 0) {
    return `00:${format(m)}:${format(s)}`;
  }

  return `${h}:${format(m)}:${format(s)}`;
}

function extractHmsFromSeconds(seconds) {
  return {
    h: Math.floor(seconds / 3600),
    m: Math.floor((seconds % 3600) / 60),
    s: Math.floor((seconds % 3600) % 60),
  };
}

function calculateDiameterFor(x, min, max) {
  if (min === max) {
    return MIN_NODE_DIAMETER;
  }
  return (
    ((x - min) / (max - min)) * (MAX_NODE_DIAMETER - MIN_NODE_DIAMETER) +
    MIN_NODE_DIAMETER
  );
}

function clearArray(array) {
  while (array.length > 0) {
    array.pop();
  }
}

function classifyStudentsByAboveAndBellowFor(cutPoint, students) {
  return {
    studentsAbove: students.filter(({ accesses }) => accesses >= cutPoint),
    studentsBellow: students.filter(({ accesses }) => accesses < cutPoint),
  };
}

function calculateAccessAvg(students) {
  let totalAccesses = 0;
  students.forEach(({ accesses }) => {
    totalAccesses += accesses;
  });
  return parseInt(totalAccesses / students.length);
}

function extractNames(students) {
  return students.map(({ name }) => name);
}
