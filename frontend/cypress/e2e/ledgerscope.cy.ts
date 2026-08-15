const apiResponse = <T>(data: T) => ({
  success: true,
  message: "ok",
  data,
});

const authenticatedUser = {
  id: 1,
  name: "Cypress Auditor",
  email: "cypress@example.test",
  phone: null,
  avatar_path: null,
  status: "active",
  mfa_enabled: false,
  permissions: [],
  roles: [{ id: 1, name: "firm_admin", display_name: "Firm Admin" }],
};

describe("LedgerScope SPA contracts", () => {
  it("renders the real login screen without demo credentials", () => {
    cy.visit("/login");

    cy.contains("Sign in to LedgerScope").should("be.visible");
    cy.get('input[type="email"]').should("have.value", "");
    cy.get('input[type="password"]').should("have.value", "");
    cy.contains("Use your audit workspace credentials.").should("be.visible");
  });

  it("redirects unauthenticated users through the route guard", () => {
    cy.intercept("GET", "**/api/v1/auth/me", {
      statusCode: 401,
      body: {
        success: false,
        code: "unauthorized",
        message: "Unauthenticated.",
      },
    }).as("sessionCheck");

    cy.visit("/dashboard");
    cy.wait("@sessionCheck");
    cy.url().should("include", "/login");
    cy.contains("Sign in to LedgerScope").should("be.visible");
  });

  it("queues a real report only after a valid API scope is loaded", () => {
    cy.intercept("GET", "**/api/v1/auth/me", {
      statusCode: 200,
      body: apiResponse(authenticatedUser),
    }).as("sessionCheck");
    cy.intercept("GET", "**/api/v1/companies", {
      statusCode: 200,
      body: apiResponse([
        {
          id: 1,
          name: "Cypress Company",
          legal_name: "Cypress Company Ltd",
          industry: "Audit",
          status: "active",
        },
      ]),
    }).as("companies");
    cy.intercept("GET", "**/api/v1/companies/1/fiscal-years", {
      statusCode: 200,
      body: apiResponse([
        {
          id: 10,
          company_id: 1,
          year: 2026,
          start_date: "2026-01-01",
          end_date: "2026-12-31",
          status: "open",
        },
      ]),
    }).as("fiscalYears");
    cy.intercept("GET", "**/api/v1/companies/1/fiscal-years/10/periods", {
      statusCode: 200,
      body: apiResponse([
        {
          id: 20,
          company_id: 1,
          fiscal_year_id: 10,
          period_name: "2026-01",
          period_type: "monthly",
          start_date: "2026-01-01",
          end_date: "2026-01-31",
          status: "open",
          is_locked: false,
        },
      ]),
    }).as("periods");
    cy.intercept("GET", "**/api/v1/companies/1/engagements", {
      statusCode: 200,
      body: apiResponse([]),
    }).as("engagements");
    cy.intercept("GET", "**/api/v1/notifications*", {
      statusCode: 200,
      body: {
        ...apiResponse([]),
        meta: {
          current_page: 1,
          last_page: 1,
          per_page: 20,
          total: 0,
          from: null,
          to: null,
        },
      },
    }).as("notifications");
    cy.intercept("GET", "**/api/v1/companies/1/reports**", {
      statusCode: 200,
      body: {
        ...apiResponse([]),
        meta: {
          current_page: 1,
          last_page: 1,
          per_page: 20,
          total: 0,
          from: null,
          to: null,
        },
      },
    }).as("reports");
    cy.intercept("POST", "**/api/v1/companies/1/reports/generate", {
      statusCode: 201,
      body: apiResponse({
        id: 30,
        company_id: 1,
        report_type: "trial_balance",
        title: "Trial balance report",
        type: "trial_balance",
        name: "Trial balance report",
        version: "30",
        status: "queued",
        generated_at: null,
        format: "pdf",
      }),
    }).as("generateReport");

    cy.visit("/reports");
    cy.wait([
      "@sessionCheck",
      "@companies",
      "@fiscalYears",
      "@periods",
      "@engagements",
      "@reports",
      "@notifications",
    ]);
    cy.get("h1").should("contain.text", "Reporting Hub");
    cy.get("main").should("contain.text", "No reports generated");

    cy.contains("Trial balance").click();
    cy.wait("@generateReport");
    cy.contains("Report generation queued.").should("be.visible");
    cy.contains("Report generation started.").should("not.exist");
  });

  it("authenticates against the full Docker stack", function () {
    cy.env<{ e2eEmail?: string; e2ePassword?: string }>([
      "e2eEmail",
      "e2ePassword",
    ]).then(({ e2eEmail, e2ePassword }) => {
      const email = e2eEmail?.trim();
      const password = e2ePassword?.trim();

      if (!email || !password) {
        this.skip();
        return;
      }

      cy.intercept("POST", "**/api/v1/auth/login").as("loginRequest");
      cy.visit("/login");
      cy.get('input[type="email"]').type(email);
      cy.get('input[type="password"]').type(password);
      cy.contains("button", "Sign In").click();

      cy.wait("@loginRequest").its("response.statusCode").should("eq", 200);
      cy.getCookie("ledgerscope-session").should("exist");
      cy.url().should("include", "/dashboard");
      cy.contains("Dashboard").should("be.visible");
      cy.intercept("GET", "**/api/v1/auth/me").as("sessionRequest");
      cy.visit("/companies");
      cy.wait("@sessionRequest").its("response.statusCode").should("eq", 200);
      cy.contains("PT Tech Nusantara").should("be.visible");

      const authenticatedSmokeRoutes = [
        ["/chart-of-accounts", "Chart of Accounts"],
        ["/journal-entries", "Journal Entries"],
        ["/audit-engagements", "Audit Engagements"],
        ["/evidence", "Document Request List"],
        ["/reports", "Reporting Hub"],
        ["/notifications", "Notifications"],
      ] as const;

      for (const [path, heading] of authenticatedSmokeRoutes) {
        cy.visit(path);
        cy.get("main").contains(heading).should("be.visible");
      }
    });
  });
});
