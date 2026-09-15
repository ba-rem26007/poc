import time
import urllib.request
from playwright.sync_api import sync_playwright

def test_frontend_e2e_flow():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context()
        page = context.new_page()

        # Route https://localhost/api to http://localhost:8000/api
        def handle_route(route):
            url = route.request.url
            if "https://localhost/api" in url:
                new_url = url.replace("https://localhost/api", "http://localhost:8000/api")
                req = urllib.request.Request(
                    new_url,
                    data=route.request.post_data_buffer,
                    headers={**route.request.headers, 'Host': 'localhost:8000'},
                    method=route.request.method
                )
                try:
                    with urllib.request.urlopen(req) as response:
                        body = response.read()
                        route.fulfill(
                            status=response.status,
                            content_type=response.headers.get('Content-Type', 'application/json'),
                            headers={"Access-Control-Allow-Origin": "*"},
                            body=body
                        )
                except urllib.error.HTTPError as e:
                    body = e.read()
                    route.fulfill(
                        status=e.code,
                        content_type="application/json",
                        headers={"Access-Control-Allow-Origin": "*"},
                        body=body
                    )
            else:
                route.continue_()

        page.route("**/*", handle_route)

        page.goto("http://localhost:5173")
        page.wait_for_selector("text=Catalog Products", timeout=10000)

        # Take E2E Verification Screenshot
        page.screenshot(path="/home/jules/verification/verification.png")
        browser.close()
        print("Playwright E2E test completed successfully!")

if __name__ == "__main__":
    test_frontend_e2e_flow()
