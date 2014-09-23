<?php

class SongsTest extends LocalWebTestCase
{
    public function testSongDoesntExist(){
    	$this->client->get('/music/1');
        $this->assertEquals(404, $this->client->response->status());
        $this->assertEquals('application/json', $this->client->response['Content-Type']);
    }

    public function testSongExists(){
        
        $this->client->get('/music/1432');
        echo 'GET /music/1432 got me a '.$this->client->response->status().', body= '.$this->client->response->body();
        $this->assertEquals(200, $this->client->response->status());
        $this->assertEquals('application/json', $this->client->response['Content-Type']);

        $songData = json_decode($this->client->response->body());

        $this->assertSame("ELECTRO HOUSE TOP 40 2011 MIX", $songData['title']);
    }
}