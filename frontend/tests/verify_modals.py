from playwright.sync_api import sync_playwright

def verify():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        page.goto('http://localhost:5173')
        page.wait_for_timeout(1000)

        # Click JWT Login
        page.click('button:has-text("JWT Login")')
        page.wait_for_timeout(500)

        # Click Forgot Password
        page.click('button:has-text("Forgot Password?")')
        page.wait_for_timeout(500)

        page.screenshot(path='/home/jules/verification/verification.png')
        browser.close()

if __name__ == '__main__':
    verify()
