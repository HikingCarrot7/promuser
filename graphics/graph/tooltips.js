function addGraphTooltips() {
  cy.elements().forEach(function (ele) {
    makePopper(ele);
  });

  cy.elements().unbind('mouseover');
  cy.elements().bind('mouseover', (event) => event.target.tippy.show());

  cy.elements().unbind('mouseout');
  cy.elements().bind('mouseout', (event) => event.target.tippy.hide());
}

function makePopper(ele) {
  const ref = ele.popperRef(); // used only for positioning
  ele.tippy = tippy(ref, {
    content: () => {
      const content = document.createElement('div');
      const { label, avgTimeSpentLabel } = ele.data();
      if (avgTimeSpentLabel) {
        content.innerHTML = `${label} (${avgTimeSpentLabel})`;
      } else {
        content.innerHTML = label;
      }
      return content;
    },
    trigger: 'manual', // probably want manual mode
  });
}
