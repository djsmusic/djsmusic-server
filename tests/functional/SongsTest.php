<?php

class SongsTest extends LocalWebTestCase
{
    public function testSongDoesntExist(){
    	$this->client->get('/music/2');
        $this->assertEquals(404, $this->client->response->status());
        $this->assertEquals('application/json', $this->client->response['Content-Type']);
    }

    public function testSongExists(){
        
        $this->client->get('/music/1');
        $this->assertEquals(200, $this->client->response->status());
        $this->assertEquals('application/json', $this->client->response['Content-Type']);

        $songData = json_decode($this->client->response->body());

        $this->assertSame("Demo song", $songData['title']);
    }
}