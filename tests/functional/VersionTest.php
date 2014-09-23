<?php

class VersionTest extends LocalWebTestCase
{
    public function testVersion(){
        $this->client->get('/');
        $this->assertEquals(200, $this->client->response->status());
        $this->assertEquals('application/json', $this->client->response['Content-Type']);
        $this->assertSame('"DJs Music API v'.$this->app->config('version').'"', $this->client->response->body());
    }
}