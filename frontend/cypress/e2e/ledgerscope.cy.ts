describe('LedgerScope frontend rebuild', () => {
  it('shows login and enters demo dashboard', () => {
    cy.visit('/login');
    cy.contains('Sign in to LedgerScope');
    cy.contains('Sign In').click();
    cy.contains('Financial Overview');
  });

  it('navigates journal creation and shows post confirmation', () => {
    cy.visit('/journal-entries');
    cy.contains('New Journal').click();
    cy.contains('Create New Journal Entry');
    cy.contains('Post Journal').click();
    cy.contains('Posted journals become immutable');
  });

  it('shows evidence upload and report generation flows', () => {
    cy.visit('/evidence');
    cy.contains('Document Request List');
    cy.contains('Evidence upload needs backend endpoint');
    cy.visit('/reports');
    cy.contains('Generate New Report').click();
    cy.contains('Report generation started');
  });
});
