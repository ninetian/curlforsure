#!/usr/bin/env python

import BaseHTTPServer
import json
import random
import time

HOST_NAME = 'localhost'
PORT_NUMBER = 8000

def rand_code():
  codes = [200, 201, 429, 500, 503, 504, 507, 509, 522]
  return random.choice(codes)

def send_rand_response(s):
  code = rand_code()
  s.send_response(code)
  s.send_header("Content-type", "application/json")
  if code == 429:
    s.send_header("Retry-After", "10")
  s.end_headers()
  s.wfile.write(json.dumps({ "code" : code }))

# simple http server
class MyHandler(BaseHTTPServer.BaseHTTPRequestHandler):
  def do_GET(s):
    send_rand_response(s)

  def do_POST(s):
    send_rand_response(s)

  def do_PUT(s):
    send_rand_response(s)

if __name__ == '__main__':
  server_class = BaseHTTPServer.HTTPServer
  httpd = server_class((HOST_NAME, PORT_NUMBER), MyHandler)
  print time.asctime(), "Server Start - %s:%s" % (HOST_NAME, PORT_NUMBER)
  try:
      httpd.serve_forever()
  except KeyboardInterrupt:
      pass
  httpd.server_close()
  print time.asctime(), "Server Stop - %s:%s" % (HOST_NAME, PORT_NUMBER)
