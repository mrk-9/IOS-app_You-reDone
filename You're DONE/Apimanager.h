//
//  Apimanager.h
//  You're DONE
//
//  Created by Leo Lorenz on 2/16/16.
//  Copyright © 2016 Leo Lorenz. All rights reserved.
//

#import <Foundation/Foundation.h>

@interface Apimanager : NSObject
{
    
}

-(void)callAPI:(NSString*)api_name withParams:(NSDictionary*)params success:(void (^)(NSData *data))successBlock error:(void(^)(NSError *error))errorBlock;

@end
